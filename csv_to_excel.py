#!/usr/bin/env python3
import pandas as pd
import sys

def csv_to_excel(csv_file, excel_file):
    """Convert CSV file to Excel format"""
    try:
        # Read CSV file
        df = pd.read_csv(csv_file)
        
        # Write to Excel
        df.to_excel(excel_file, index=False, engine='openpyxl')
        
        print(f"✅ Successfully converted {csv_file} to {excel_file}")
        print(f"📊 Data: {len(df)} rows × {len(df.columns)} columns")
        
        # Show multilingual columns
        multilingual_cols = [col for col in df.columns if '_ku' in col or '_ar' in col]
        print(f"🌍 Multilingual columns: {len(multilingual_cols)}")
        for col in multilingual_cols:
            print(f"   - {col}")
            
        return True
    except Exception as e:
        print(f"❌ Error: {e}")
        return False

if __name__ == "__main__":
    csv_file = "test_multilingual_import.csv"
    excel_file = "test_multilingual_import.xlsx"
    
    success = csv_to_excel(csv_file, excel_file)
    if success:
        sys.exit(0)
    else:
        sys.exit(1)