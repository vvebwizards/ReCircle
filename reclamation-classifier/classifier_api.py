from fastapi import FastAPI
from pydantic import BaseModel
import joblib
import re
import string
from fastapi.middleware.cors import CORSMiddleware

# Load Sklearn model and vectorizer
model = joblib.load("sklearn_model.joblib")
vectorizer = joblib.load("sklearn_vectorizer.joblib")

# FastAPI app
app = FastAPI()

# Add CORS middleware
app.add_middleware(
    CORSMiddleware,
    allow_origins=["http://127.0.0.1:8000"],  # Laravel app URL
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

class ReclamationRequest(BaseModel):
    description: str

def preprocess_text(text):
    text = text.lower()
    text = text.translate(str.maketrans('', '', string.punctuation))
    text = re.sub(r'\d+', '', text)
    text = re.sub(r'\s+', ' ', text).strip()
    return text

@app.post("/classify")
def classify_reclamation(req: ReclamationRequest):
    clean_text = preprocess_text(req.description)
    severity = model.predict([clean_text])[0]
    return {"severity": severity}  # Changed from category to severity

@app.get("/")
def read_root():
    return {"message": "Severity Classification API is running!"}