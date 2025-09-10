-- Fix translations table id field to have AUTO_INCREMENT
-- Run this SQL directly on the database

-- Check current table structure
DESCRIBE translations;

-- Fix the id column to have AUTO_INCREMENT if it's missing
-- This will handle cases where the table was created manually or migration didn't run properly
ALTER TABLE translations MODIFY COLUMN id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY;

-- Verify the fix
DESCRIBE translations;

-- Check if there are any existing records that might have id conflicts
SELECT COUNT(*) as total_records FROM translations;

-- If there are existing records with id = 0 or NULL, this will fix them
UPDATE translations SET id = NULL WHERE id = 0;

-- Alternative approach - if the above doesn't work, recreate the table:
/*
-- Backup existing data
CREATE TABLE translations_backup AS SELECT * FROM translations WHERE translationable_id IS NOT NULL;

-- Drop and recreate table with proper structure
DROP TABLE translations;

CREATE TABLE translations (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    translationable_type VARCHAR(255) NOT NULL,
    translationable_id BIGINT UNSIGNED NOT NULL,
    locale VARCHAR(255) NOT NULL,
    `key` VARCHAR(255) NOT NULL,
    value TEXT NOT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    INDEX translations_translationable_type_translationable_id_index (translationable_type, translationable_id)
);

-- Restore data (excluding id to let AUTO_INCREMENT handle it)
INSERT INTO translations (translationable_type, translationable_id, locale, `key`, value, created_at, updated_at)
SELECT translationable_type, translationable_id, locale, `key`, value, 
       COALESCE(created_at, NOW()), COALESCE(updated_at, NOW())
FROM translations_backup;

-- Clean up backup table
DROP TABLE translations_backup;
*/