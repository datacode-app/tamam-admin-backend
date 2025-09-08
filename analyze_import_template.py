#!/usr/bin/env python3
import pandas as pd
import sys
import os

def analyze_template_for_import(file_path):
    """Analyze what records would be imported from the template"""
    try:
        if not os.path.exists(file_path):
            print(f"❌ File not found: {file_path}")
            return False
            
        # Read Excel file
        df = pd.read_excel(file_path, engine='openpyxl')
        
        print(f"📁 Analyzing: {file_path}")
        print(f"📊 Data: {len(df)} records × {len(df.columns)} columns")
        print("=" * 80)
        
        print("\n🏪 STORES THAT WOULD BE IMPORTED:")
        print("=" * 80)
        
        for idx, row in df.iterrows():
            print(f"\n📍 Store #{idx + 1}:")
            print(f"   Owner: {row['ownerFirstName']} {row['ownerLastName']}")
            print(f"   Email: {row['email']}")
            print(f"   Phone: {row['phone']}")
            print(f"   Store Name (English): {row['storeName']}")
            
            # Check for multilingual data
            if 'storeName_ku' in row and pd.notna(row['storeName_ku']) and row['storeName_ku']:
                print(f"   Store Name (Kurdish): {row['storeName_ku']}")
            if 'storeName_ar' in row and pd.notna(row['storeName_ar']) and row['storeName_ar']:
                print(f"   Store Name (Arabic): {row['storeName_ar']}")
                
            print(f"   Address (English): {row['Address']}")
            
            if 'Address_ku' in row and pd.notna(row['Address_ku']) and row['Address_ku']:
                print(f"   Address (Kurdish): {row['Address_ku']}")
            if 'Address_ar' in row and pd.notna(row['Address_ar']) and row['Address_ar']:
                print(f"   Address (Arabic): {row['Address_ar']}")
                
            print(f"   Zone ID: {row['zone_id']}")
            print(f"   Module ID: {row['module_id']}")
            print(f"   Status: {row['Status']}")
            
        print("\n" + "=" * 80)
        print("📊 DATABASE IMPACT ANALYSIS:")
        print("=" * 80)
        
        # Count records that would be added
        new_stores = len(df)
        new_vendors = len(df)
        
        # Count translations
        translation_count = 0
        for idx, row in df.iterrows():
            # Check each translatable field
            if 'storeName_ku' in row and pd.notna(row['storeName_ku']) and row['storeName_ku']:
                translation_count += 1
            if 'storeName_ar' in row and pd.notna(row['storeName_ar']) and row['storeName_ar']:
                translation_count += 1
            if 'Address_ku' in row and pd.notna(row['Address_ku']) and row['Address_ku']:
                translation_count += 1
            if 'Address_ar' in row and pd.notna(row['Address_ar']) and row['Address_ar']:
                translation_count += 1
                
        print(f"\n📈 Records that would be added to database:")
        print(f"   • {new_vendors} new records in 'vendors' table")
        print(f"   • {new_stores} new records in 'stores' table")
        print(f"   • {translation_count} new records in 'translations' table")
        print(f"   • Total database records: {new_vendors + new_stores + translation_count}")
        
        print("\n🌍 Multilingual Data Summary:")
        kurdish_count = 0
        arabic_count = 0
        
        for idx, row in df.iterrows():
            if ('storeName_ku' in row and pd.notna(row['storeName_ku']) and row['storeName_ku']) or \
               ('Address_ku' in row and pd.notna(row['Address_ku']) and row['Address_ku']):
                kurdish_count += 1
            if ('storeName_ar' in row and pd.notna(row['storeName_ar']) and row['storeName_ar']) or \
               ('Address_ar' in row and pd.notna(row['Address_ar']) and row['Address_ar']):
                arabic_count += 1
                
        print(f"   • Stores with Kurdish translations: {kurdish_count}")
        print(f"   • Stores with Arabic translations: {arabic_count}")
        
        # Check for potential duplicates
        print("\n⚠️  Duplicate Check (by email):")
        emails = df['email'].tolist()
        unique_emails = set(emails)
        if len(emails) == len(unique_emails):
            print("   ✅ No duplicate emails found - all stores are unique")
        else:
            print(f"   ⚠️ Found {len(emails) - len(unique_emails)} duplicate emails")
            
        return True
        
    except Exception as e:
        print(f"❌ Error analyzing file: {e}")
        return False

if __name__ == "__main__":
    file_path = "/Users/hooshyar/Downloads/stores_multilang_template (4).xlsx"
    
    print("🔍 TEMPLATE IMPORT ANALYSIS")
    print("=" * 80)
    
    success = analyze_template_for_import(file_path)
    
    if not success:
        print("\n❌ Could not analyze the template file")
        sys.exit(1)
    
    print("\n" + "=" * 80)
    print("✅ Analysis complete!")
    print("\n📝 NOTE: These records would be NEW additions to the database.")
    print("   The import process would create new vendor accounts and stores.")
    print("   Translations would be stored in the translations table with morphMany relationship.")
    sys.exit(0)