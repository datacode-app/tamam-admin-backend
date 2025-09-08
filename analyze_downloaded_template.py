#!/usr/bin/env python3
import pandas as pd
import sys
import os

def analyze_excel_file(file_path):
    """Analyze the downloaded Excel template file"""
    try:
        if not os.path.exists(file_path):
            print(f"❌ File not found: {file_path}")
            return False
            
        # Read Excel file
        df = pd.read_excel(file_path, engine='openpyxl')
        
        print(f"📁 File: {file_path}")
        print(f"📊 Data: {len(df)} rows × {len(df.columns)} columns")
        print(f"💾 File size: {round(os.path.getsize(file_path) / 1024, 2)}KB")
        
        print(f"\n🔍 Column Analysis:")
        print(f"{'Index':<5} {'Column Name':<25} {'Sample Data'}")
        print("=" * 70)
        
        for i, col in enumerate(df.columns):
            sample_data = str(df[col].iloc[0]) if len(df) > 0 else '[empty]'
            if len(sample_data) > 30:
                sample_data = sample_data[:27] + "..."
            print(f"{i+1:<5} {col:<25} {sample_data}")
        
        # Check for multilingual fields
        multilingual_cols = [col for col in df.columns if '_ku' in col or '_ar' in col]
        print(f"\n🌍 Multilingual Analysis:")
        print(f"Total multilingual columns: {len(multilingual_cols)}")
        
        if multilingual_cols:
            print("✅ Multilingual fields found:")
            for col in multilingual_cols:
                lang = "Kurdish Sorani" if '_ku' in col else "Arabic"
                base_field = col.replace('_ku', '').replace('_ar', '')
                print(f"   • {col} ({lang} translation for '{base_field}')")
        else:
            print("❌ No multilingual fields found!")
            
        # Check if template has proper data
        if len(df) > 0:
            print(f"\n📋 Sample Data (First Row):")
            for col in df.columns[:10]:  # Show first 10 columns
                value = df[col].iloc[0]
                print(f"   {col}: {value}")
            if len(df.columns) > 10:
                print(f"   ... and {len(df.columns) - 10} more columns")
        
        return True
        
    except Exception as e:
        print(f"❌ Error analyzing file: {e}")
        return False

if __name__ == "__main__":
    file_path = "/Users/hooshyar/Downloads/stores_multilang_template (2).xlsx"
    
    print("🧪 DOWNLOADED TEMPLATE ANALYSIS")
    print("=" * 50)
    
    success = analyze_excel_file(file_path)
    
    if success:
        print(f"\n🎯 CONCLUSION:")
        print("The downloaded template file was successfully analyzed.")
        print("Check the multilingual fields section above to verify functionality.")
    else:
        print(f"\n❌ ANALYSIS FAILED")
        print("Could not analyze the downloaded template file.")
    
    sys.exit(0 if success else 1)