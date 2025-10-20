import requests
import sys
import os

def test_detect_materials(image_path):
    """Test the /detect_materials endpoint with a sample image."""
    if not os.path.exists(image_path):
        print(f"Error: Image file not found at {image_path}")
        return
        
    print(f"Testing with image: {image_path}")
    
    try:
        url = 'http://localhost:5000/detect_materials'
        files = {'file': open(image_path, 'rb')}
        
        print(f"Sending request to {url}...")
        response = requests.post(url, files=files)
        
        print(f"Status code: {response.status_code}")
        if response.status_code == 200:
            data = response.json()
            print("\nDetected materials:")
            if data.get('materials'):
                for material in data['materials']:
                    print(f"- {material['name']} ({material['confidence']}% confidence)")
            else:
                print("No materials detected.")
            
            print(f"\nTotal materials found: {data.get('count', 0)}")
        else:
            print(f"Error: {response.text}")
    except Exception as e:
        print(f"Error: {str(e)}")

if __name__ == "__main__":
    # Use a sample image if provided, otherwise use a default path
    image_path = sys.argv[1] if len(sys.argv) > 1 else "sample_image.jpg"
    test_detect_materials(image_path)