# Multilingual Features Migration Guide

This guide covers the comprehensive multilingual database migrations created for the Tamam platform.

## 🎯 Overview

The multilingual migration system sets up complete language support for:
- **Arabic** (`ar`)
- **Kurdish Sorani** (`ckb`) with fallback aliases (`ku`, `kmr`, `sorani`)
- **English** (`en`) as default/fallback

## 📋 Migrations Created

### 1. **Comprehensive Translations Table** 
**File**: `2025_01_10_190000_create_comprehensive_translations_table.php`

Creates a robust translations table with:
- ✅ Proper AUTO_INCREMENT `id` field (fixes the original issue)
- ✅ Polymorphic relationships (`translationable_type`, `translationable_id`)
- ✅ Language support (`locale`) with Kurdish aliases
- ✅ Performance indexes for fast queries
- ✅ Unique constraints to prevent duplicates
- ✅ Audit fields (`created_by`, `updated_by`)

```sql
CREATE TABLE translations (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    translationable_type VARCHAR(255) NOT NULL,
    translationable_id BIGINT UNSIGNED NOT NULL,
    locale VARCHAR(10) NOT NULL,
    key VARCHAR(100) NOT NULL,
    value LONGTEXT NULL,
    is_active BOOLEAN DEFAULT true,
    created_by VARCHAR(50) NULL,
    updated_by VARCHAR(50) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    UNIQUE KEY idx_unique_translation (translationable_type, translationable_id, locale, key)
);
```

### 2. **Supported Languages Management**
**File**: `2025_01_10_191000_create_supported_languages_table.php`

- ✅ Centralized language configuration
- ✅ RTL/LTR direction support
- ✅ Language aliases for Kurdish variants
- ✅ Pre-populated with Arabic, Kurdish, English

### 3. **Performance Indexes**
**File**: `2025_01_10_192000_add_multilingual_indexes_to_core_tables.php`

Adds optimized indexes to:
- ✅ `stores` table (`name`, `address`, `zone_id`, `module_id`)
- ✅ `items` table (`name`, `description`, `store_id`)
- ✅ `categories` table (`name`, `parent_id`)
- ✅ `banners` and `coupons` tables

### 4. **Data Migration**
**File**: `2025_01_10_193000_migrate_existing_data_to_multilingual.php`

- ✅ Preserves existing store names and addresses as English translations
- ✅ Preserves existing item names and descriptions
- ✅ Preserves existing category, banner, and coupon data
- ✅ Uses chunking for memory efficiency with large datasets
- ✅ Uses `insertOrIgnore` to prevent duplicate translations

### 5. **Settings & Constraints**
**File**: `2025_01_10_194000_add_multilingual_settings_and_constraints.php`

- ✅ Adds multilingual configuration to `business_settings`
- ✅ Creates translation validation table for quality control
- ✅ Creates translation audit table for change tracking
- ✅ Adds stored procedures for data cleanup

### 6. **Import/Export Logging**
**File**: `2025_01_10_195000_create_multilingual_import_export_logs.php`

- ✅ Tracks all import/export operations
- ✅ Records processing metrics and performance data
- ✅ Logs errors and warnings for troubleshooting
- ✅ Maintains record-level processing details

## 🚀 Installation

### Option 1: Automated Script (Recommended)
```bash
chmod +x run_multilingual_migrations.sh
./run_multilingual_migrations.sh
```

### Option 2: Manual Migration
```bash
# Run migrations in order
php artisan migrate --path=database/migrations/2025_01_10_190000_create_comprehensive_translations_table.php
php artisan migrate --path=database/migrations/2025_01_10_191000_create_supported_languages_table.php
php artisan migrate --path=database/migrations/2025_01_10_192000_add_multilingual_indexes_to_core_tables.php
php artisan migrate --path=database/migrations/2025_01_10_193000_migrate_existing_data_to_multilingual.php
php artisan migrate --path=database/migrations/2025_01_10_194000_add_multilingual_settings_and_constraints.php
php artisan migrate --path=database/migrations/2025_01_10_195000_create_multilingual_import_export_logs.php

# Run any remaining migrations
php artisan migrate

# Clear caches
php artisan config:clear && php artisan cache:clear
```

### Option 3: Direct Database Fix (If needed)
```sql
-- Fix existing translations table
ALTER TABLE translations MODIFY COLUMN id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY;

-- Or recreate if necessary
DROP TABLE translations;
-- Then run migrations
```

## 🔧 Verification

After migration, verify the setup:

### Check Translations Table
```bash
php artisan tinker
>>> DB::select('DESCRIBE translations');
>>> DB::table('translations')->count();
```

### Check Supported Languages
```bash
>>> DB::table('supported_languages')->get(['code', 'name', 'native_name']);
```

### Check Business Settings
```bash
>>> DB::table('business_settings')->whereIn('key', ['multilingual_enabled', 'supported_languages'])->get();
```

## 📊 Database Schema Summary

### Core Tables Created/Modified:
1. **`translations`** - Main multilingual storage (recreated with proper schema)
2. **`supported_languages`** - Language configuration
3. **`translation_validations`** - Quality control
4. **`translation_audits`** - Change tracking  
5. **`multilingual_import_logs`** - Import/export tracking
6. **`multilingual_import_records`** - Record-level import details
7. **`multilingual_export_stats`** - Export analytics

### Enhanced Tables (Indexes Added):
- `stores`, `items`, `categories`, `banners`, `coupons`

### Business Settings Added:
- `multilingual_enabled` → `true`
- `default_language` → `'en'`
- `supported_languages` → `['en', 'ar', 'ckb']`
- `rtl_languages` → `['ar', 'ckb']`

## 🎯 Key Features Enabled

### ✅ **Fixed Original Issue**
- `translations` table now has proper AUTO_INCREMENT `id` field
- No more "Field 'id' doesn't have a default value" errors

### ✅ **Round-Trip Compatibility** 
- Export stores with Arabic/Kurdish translations
- Modify in Excel with `name_ar`, `name_ckb`, `address_ar`, `address_ckb` columns  
- Re-import successfully with all translations preserved

### ✅ **Performance Optimized**
- Strategic indexes on translatable fields
- Efficient polymorphic queries
- Memory-optimized bulk operations

### ✅ **Data Integrity**
- Unique constraints prevent duplicate translations
- Foreign key constraints maintain referential integrity
- Audit trail tracks all changes

### ✅ **Kurdish Language Support**
- Supports multiple Kurdish variants (`ckb`, `ku`, `kmr`, `sorani`)
- Intelligent fallback system
- RTL text direction support

## 🚨 Important Notes

### **Backup First**
Always backup your database before running migrations:
```bash
mysqldump -u username -p database_name > backup_before_multilingual.sql
```

### **Large Datasets**
For stores/items tables with >10K records, the data migration uses chunking but may still take time. Monitor the process.

### **Kurdish Aliases**
The system supports these Kurdish language codes:
- `ckb` (Central Kurdish/Sorani) - Primary
- `ku` (Kurdish) - Alias  
- `kmr` (Kurmanji) - Alias
- `sorani` - Alias
- `kurdish` - Alias

### **Rollback Strategy**
These migrations modify existing data. To rollback:
1. Restore from backup
2. Or run: `php artisan migrate:rollback --step=6`

## 🎊 Success Indicators

After successful migration, you should see:

1. **✅ Translations table** with AUTO_INCREMENT id field
2. **✅ Existing data** preserved as English translations
3. **✅ Multilingual import/export** working without errors
4. **✅ Round-trip compatibility** functional
5. **✅ Performance indexes** improving query speed

## 🆘 Troubleshooting

### Issue: Migration fails on indexes
**Solution**: Some indexes may fail if tables don't exist. This is normal - they'll be created when the tables are added.

### Issue: Data migration takes too long
**Solution**: Increase PHP memory limit and execution time:
```php
ini_set('memory_limit', '512M');
ini_set('max_execution_time', 300);
```

### Issue: Unique constraint violations
**Solution**: Clean up duplicate data first:
```sql
-- Find duplicates
SELECT translationable_type, translationable_id, locale, `key`, COUNT(*) 
FROM translations 
GROUP BY translationable_type, translationable_id, locale, `key` 
HAVING COUNT(*) > 1;
```

---

**The multilingual migration system is now ready for production deployment! 🚀**