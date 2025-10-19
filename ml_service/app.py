from fastapi import FastAPI, File, UploadFile, Form, HTTPException
from fastapi.middleware.cors import CORSMiddleware
from pydantic import BaseModel
import pandas as pd
import joblib
import numpy as np
import torch
from PIL import Image
import io
import os
import json

# Load materials keywords mapping for fuzzy matching
MATERIALS_KEYWORDS = {}
try:
    mk_path = os.path.join(os.path.dirname(__file__), 'materials_keywords.json')
    if os.path.exists(mk_path):
        with open(mk_path, 'r', encoding='utf-8') as fh:
            MATERIALS_KEYWORDS = json.loads(fh.read())
except Exception:
    MATERIALS_KEYWORDS = {}

model = joblib.load("co2_predictor_best.pkl")  
FEATURE_COLUMNS = joblib.load("feature_columns.pkl")  

app = FastAPI(title="Waste2Product ML Service API")

# Add CORS middleware
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],  # In production, specify your frontend origin
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# Load object detection model
try:
    # Using YOLOv5 model for object detection
    object_detection_model = torch.hub.load('ultralytics/yolov5', 'yolov5s', pretrained=True)
    # Define materials that can be detected
    WASTE_MATERIALS = [
        "plastic", "wood", "metal", "glass", "paper", "cardboard", 
        "textile", "electronic", "battery", "furniture", "bottle"
    ]
    # Load optional class->material mappings from mappings.json (editable without code changes)
    mappings_path = os.path.join(os.path.dirname(__file__), 'mappings.json')
    if os.path.exists(mappings_path):
        try:
            with open(mappings_path, 'r', encoding='utf-8') as fh:
                CLASS_TO_MATERIAL = json.loads(fh.read())
        except Exception:
            CLASS_TO_MATERIAL = {}
    else:
        CLASS_TO_MATERIAL = {}
except Exception as e:
    print(f"Error loading object detection model: {e}")
    object_detection_model = None
    # Ensure CLASS_TO_MATERIAL exists even if model failed to load
    try:
        CLASS_TO_MATERIAL
    except NameError:
        CLASS_TO_MATERIAL = {}

class MaterialInput(BaseModel):
    quantity: float
    recyclability_score: float
    category: str  

@app.post("/predict_impact")
def predict_impact(data: MaterialInput):
    features = {}
    for col in FEATURE_COLUMNS:
        if col.startswith("category_"):
            cat_name = col.split("_")[1]
            features[col] = 1 if data.category == cat_name else 0
        else:
            features[col] = getattr(data, col, 0)  

    df = pd.DataFrame([features])

    co2, landfill = model.predict(df)[0]

    return {
        "predicted_co2_saved": round(float(co2), 2),
        "predicted_landfill_avoided": round(float(landfill), 2)
    }

@app.post("/detect_materials")
async def detect_materials(file: UploadFile = File(...)):
    """
    Detect materials in the uploaded waste item image
    """
    if not object_detection_model:
        raise HTTPException(status_code=503, detail="Object detection model not available")
    
    try:
        # Read and process the image
        contents = await file.read()
        img = Image.open(io.BytesIO(contents))
        
        # Run inference
        results = object_detection_model(img)
        
        # Process results
        predictions = results.pandas().xyxy[0]
        detected_objects = []
        
        import re

        # Helper: whole-word regex match (case-insensitive)
        def whole_word_in(text: str, word: str) -> bool:
            return re.search(r"\b" + re.escape(word) + r"\b", text, flags=re.IGNORECASE) is not None

        for _, row in predictions.iterrows():
            class_name = row['name'].lower()
            confidence = float(row['confidence'])

            # 1) Exact mapping from class -> material
            mapped = CLASS_TO_MATERIAL.get(class_name)
            if mapped:
                detected_objects.append({
                    "material": mapped,
                    "confidence": confidence,
                    "class": class_name,
                    "reason": "explicit_mapping"
                })
                continue

            # 2) Keyword-based whole-word mapping using MATERIALS_KEYWORDS
            keyword_mapped = None
            for material, keys in MATERIALS_KEYWORDS.items():
                for key in keys:
                    if whole_word_in(class_name, key):
                        keyword_mapped = material
                        break
                if keyword_mapped:
                    break

            if keyword_mapped:
                detected_objects.append({
                    "material": keyword_mapped,
                    "confidence": confidence,
                    "class": class_name,
                    "reason": "keyword_match"
                })
                continue

            # 3) Conservative substring fallback against the canonical WASTE_MATERIALS
            # Only accept when the material appears as a whole word inside the class_name
            substring_mapped = None
            for material in WASTE_MATERIALS:
                if whole_word_in(class_name, material):
                    substring_mapped = material
                    break

            if substring_mapped:
                detected_objects.append({
                    "material": substring_mapped,
                    "confidence": confidence,
                    "class": class_name,
                    "reason": "material_wholeword"
                })
                continue
        
        # Filter by confidence and unique materials (keep highest confidence per material)
        materials_found = {}
        for obj in detected_objects:
            material = obj['material']
            confidence = obj['confidence']

            if material not in materials_found or confidence > materials_found[material]['confidence']:
                materials_found[material] = obj

        # Build top predictions list for debug purposes
        top_preds = []
        if not predictions.empty:
            for _, row in predictions.head(5).iterrows():
                top_preds.append({'class': row['name'].lower(), 'confidence': float(row['confidence'])})

        # Format the response from the mapped materials, applying a confidence threshold
        materials = [
            {
                "name": item["material"],
                "confidence": round(item["confidence"] * 100, 2),
                "original_class": item["class"],
                "reason": item.get("reason")
            }
            for item in materials_found.values()
            if item["confidence"] >= 0.2  # 20% confidence threshold
        ]

        # If no materials mapped so far, attempt a conservative keyword-based mapping:
        # accept only if keywords point to a single unambiguous material among top predictions
        if not materials and not predictions.empty and MATERIALS_KEYWORDS:
            candidate_counts = {}
            candidate_examples = {}
            for _, row in predictions.head(5).iterrows():
                cname = row['name'].lower()
                conf = float(row['confidence'])
                for mat, keys in MATERIALS_KEYWORDS.items():
                    for key in keys:
                        if whole_word_in(cname, key):
                            candidate_counts[mat] = candidate_counts.get(mat, 0) + 1
                            candidate_examples.setdefault(mat, []).append({'class': cname, 'confidence': conf, 'key': key})
                            break

            # If exactly one material candidate exists and it has at least one match, accept it
            if len(candidate_counts) == 1:
                sole_material = next(iter(candidate_counts.keys()))
                # Use the highest-confidence example for reporting
                examples = sorted(candidate_examples.get(sole_material, []), key=lambda x: x['confidence'], reverse=True)
                best = examples[0]
                materials = [{
                    'name': sole_material,
                    'confidence': round(best['confidence'] * 100, 2),
                    'original_class': best['class'],
                    'reason': 'keyword_unambiguous'
                }]

        # Return materials (possibly empty), count, and top_predictions for debugging
        return {
            "materials": materials,
            "count": len(materials),
            "top_predictions": top_preds
        }
        
    except Exception as e:
        raise HTTPException(status_code=500, detail=f"Error processing image: {str(e)}")
