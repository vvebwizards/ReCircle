from fastapi import FastAPI, File, UploadFile
from fastapi.middleware.cors import CORSMiddleware

# Create FastAPI app
app = FastAPI(title="Simple ML Mock Service")

# Add CORS middleware
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],  
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

@app.get("/")
async def root():
    return {"message": "Waste2Product ML Service is running"}

@app.post("/detect_materials")
async def detect_materials(file: UploadFile = File(...)):
    # Just return mock data for testing
    return {
        "materials": [
            {
                "name": "plastic",
                "confidence": 95.5,
                "original_class": "plastic bottle"
            },
            {
                "name": "wood",
                "confidence": 87.2,
                "original_class": "wooden plank"
            },
            {
                "name": "metal",
                "confidence": 65.1,
                "original_class": "metal can"
            }
        ],
        "count": 3
    }