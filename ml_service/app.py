from fastapi import FastAPI
from pydantic import BaseModel
import pandas as pd
import joblib
import numpy as np

model = joblib.load("co2_predictor_best.pkl")  
FEATURE_COLUMNS = joblib.load("feature_columns.pkl")  

app = FastAPI(title="CO2 & Landfill Impact Prediction API")

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
