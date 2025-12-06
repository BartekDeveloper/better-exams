import os
import csv
import sys

def check_csv_files(root_dir):
    print(f"Checking CSV files in {root_dir}...")
    for dirpath, dirnames, filenames in os.walk(root_dir):
        for filename in filenames:
            if filename.endswith('.csv'):
                filepath = os.path.join(dirpath, filename)
                print(f"Checking {filepath}...")
                try:
                    with open(filepath, 'r', encoding='utf-8', errors='replace') as csvfile:
                        # Detect delimiter if possible, or assume ';' based on previous observation
                        sample = csvfile.read(1024)
                        csvfile.seek(0)
                        dialect = csv.Sniffer().sniff(sample)
                        
                        # Force delimiter to be ';' if sniffer fails or detects something else but we know it's likely ';'
                        # Actually, let's trust the sniffer but fallback to ';' if it fails.
                        # The user file showed ';'
                        
                        reader = csv.reader(csvfile, delimiter=';')
                        header = next(reader, None)
                        
                        if not header:
                            print(f"  Empty file: {filepath}")
                            continue

                        # Find category_name index
                        try:
                            category_index = header.index('category_name')
                        except ValueError:
                            # If not in header, maybe it's the last column as per observation
                            # But let's print a warning
                            print(f"  'category_name' not found in header of {filepath}. Header: {header}")
                            # Assuming last column based on user report might be risky if schema differs.
                            # Let's try to find it by name.
                            continue

                        for line_num, row in enumerate(reader, start=2):
                            if len(row) <= category_index:
                                print(f"  Line {line_num}: Row too short. Length {len(row)}, expected > {category_index}")
                                continue
                            
                            category_name = row[category_index]
                            if len(category_name) > 255:
                                print(f"  Line {line_num}: category_name length {len(category_name)} > 255. Value: {category_name[:50]}...")

                except Exception as e:
                    print(f"  Error reading {filepath}: {e}")

if __name__ == "__main__":
    root_directory = "/opt/lampp/htdocs/better-exams/model/data"
    check_csv_files(root_directory)
