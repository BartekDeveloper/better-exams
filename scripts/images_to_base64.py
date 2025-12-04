#!/usr/bin/env python3
"""
Convert local image files to base64 and update CSV files.
This script processes all question CSV files and converts the local images
to base64 encoding, storing them in the image_fallback field.
"""

import csv
import os
import base64
from pathlib import Path

# Directories containing the CSV files and images
DATA_DIRS = [
    "./model/data/inf_02",
    "./model/data/inf_03", 
    "./model/data/inf_04"
]

CSV_DELIMITER = ";"

def image_to_base64(image_path: str) -> str:
    """Convert an image file to base64 string."""
    try:
        if not image_path or not os.path.exists(image_path):
            return ""
        
        with open(image_path, "rb") as img_file:
            # Read the image and encode to base64
            encoded = base64.b64encode(img_file.read()).decode('utf-8')
            
            # Detect image type from extension
            ext = os.path.splitext(image_path)[1].lower()
            mime_type = {
                '.jpg': 'image/jpeg',
                '.jpeg': 'image/jpeg',
                '.png': 'image/png',
                '.gif': 'image/gif',
                '.webp': 'image/webp',
                '.svg': 'image/svg+xml'
            }.get(ext, 'image/jpeg')
            
            # Return as data URI
            return f"data:{mime_type};base64,{encoded}"
    except Exception as e:
        print(f"  ⚠️  Error encoding {image_path}: {e}")
        return ""

def process_csv(csv_path: str, data_dir: str) -> int:
    """Process a single CSV file and convert images to base64."""
    if not os.path.exists(csv_path):
        print(f"  ⚠️  CSV not found: {csv_path}")
        return 0
    
    # Read the CSV
    rows = []
    with open(csv_path, 'r', newline='', encoding='utf-8') as f:
        reader = csv.DictReader(f, delimiter=CSV_DELIMITER)
        fieldnames = reader.fieldnames
        
        for row in reader:
            # Get the local image path
            local_image = row.get('image', '')
            
            if local_image:
                # Construct full path relative to the data directory
                full_image_path = os.path.join(data_dir, local_image)
                
                # Convert to base64
                base64_data = image_to_base64(full_image_path)
                
                # Update the image_fallback field with base64 or keep URL if conversion failed
                if base64_data:
                    row['image_fallback'] = base64_data
                # If base64_data is empty, keep the existing image_fallback (URL)
            
            rows.append(row)
    
    # Write back to CSV
    with open(csv_path, 'w', newline='', encoding='utf-8') as f:
        writer = csv.DictWriter(f, fieldnames=fieldnames, delimiter=CSV_DELIMITER)
        writer.writeheader()
        writer.writerows(rows)
    
    return len(rows)

def main():
    print("🔄 Converting local images to base64...\n")
    
    total_processed = 0
    
    for data_dir in DATA_DIRS:
        csv_path = os.path.join(data_dir, "questions.csv")
        category = os.path.basename(data_dir)
        
        print(f"📁 Processing {category}...")
        
        count = process_csv(csv_path, data_dir)
        total_processed += count
        
        print(f"   ✅ Processed {count} questions\n")
    
    print(f"🎉 Done! Converted images for {total_processed} total questions")
    print("\n💡 The CSV files have been updated with base64-encoded images in the 'image_fallback' field")

if __name__ == "__main__":
    main()
