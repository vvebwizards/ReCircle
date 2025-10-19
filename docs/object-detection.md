# Object Detection Feature for Waste Items

This feature enables automatic detection of materials in waste item photos, adding appropriate tags (#plastic, #wood, etc.) to make them more searchable and categorizable.

## How It Works

1. When a user uploads a waste item image, the system sends the first image to the ML service for analysis.
2. The ML service uses YOLOv5, a pre-trained object detection model, to identify materials in the image.
3. Detected materials are converted to tags and attached to the waste item with confidence scores.
4. Users can also manually add tags when creating a waste item.

## Setup Instructions

### 1. Database Migration

Run the migrations to create tags and taggables tables:

```bash
php artisan migrate
```

### 2. Seed the Database with Common Tags

```bash
php artisan db:seed --class=TagSeeder
```

### 3. ML Service Setup

Make sure to install the required Python packages in the ml_service directory:

```bash
cd ml_service
pip install -r requirements.txt
```

### 4. Configure the ML Service URL

Add the following to your `.env` file:

```
ML_SERVICE_URL=http://localhost:8000
```

### 5. Run the ML Service

```bash
cd ml_service
uvicorn app:app --reload
```

## Usage

When creating a new waste item through the API, you can:

1. Upload images as usual (the first image will be used for material detection)
2. Optionally include a comma-separated list of tags in the "tags" field
3. The system will combine auto-detected tags with manual tags

The response will include all tags with their confidence scores for auto-detected tags.

## API Response Example

```json
{
  "data": {
    "id": 1,
    "title": "Used wooden furniture",
    "images": ["storage/images/waste-items/abc123.jpg"],
    "primary_image": "storage/images/waste-items/abc123.jpg",
    "estimated_weight": 15.5,
    "condition": "good",
    "location": { "lat": 40.7128, "lng": -74.006 },
    "notes": "Slightly used wooden desk",
    "generator_id": 1,
    "tags": [
      {
        "id": 1,
        "name": "wood",
        "display_name": "wood",
        "is_auto_generated": true,
        "confidence": 97
      },
      {
        "id": 2,
        "name": "furniture",
        "display_name": "furniture", 
        "is_auto_generated": true,
        "confidence": 89
      },
      {
        "id": 3,
        "name": "desk",
        "display_name": "desk",
        "is_auto_generated": false,
        "confidence": null
      }
    ],
    "created_at": "2025-10-18T12:00:00.000000Z",
    "updated_at": "2025-10-18T12:00:00.000000Z"
  }
}
```