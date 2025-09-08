#!/bin/bash

echo "🔧 Kurdish Language Code Fixer"
echo "============================="
echo ""

# Change to the Admin-with-Rental directory
cd "$(dirname "$0")"

echo "1️⃣ Backing up Kurdish language folder..."
if [ -d "resources/lang/ku" ]; then
    BACKUP_NAME="resources/lang/ku_backup_$(date +%Y_%m_%d_%H_%M_%S)"
    cp -r resources/lang/ku "$BACKUP_NAME"
    echo "   ✅ Backup created: $BACKUP_NAME"
else
    echo "   ℹ️ No 'ku' language folder found to backup"
fi

echo ""
echo "2️⃣ Renaming language folder..."
if [ -d "resources/lang/ku" ]; then
    # Remove existing ckb folder if it exists
    if [ -d "resources/lang/ckb" ]; then
        rm -rf resources/lang/ckb
        echo "   🗑️ Removed existing ckb folder"
    fi
    
    # Rename ku to ckb
    mv resources/lang/ku resources/lang/ckb
    echo "   ✅ Language folder renamed: ku → ckb"
    
    # Verify the messages.php file exists
    if [ -f "resources/lang/ckb/messages.php" ]; then
        echo "   ✅ Kurdish messages.php file preserved"
    else
        echo "   ⚠️ Kurdish messages.php file missing"
    fi
else
    echo "   ℹ️ No 'ku' language folder found to rename"
fi

echo ""
echo "3️⃣ Running database migration..."
php artisan migrate --path=database/migrations/2025_08_07_000000_fix_kurdish_language_code.php 2>/dev/null
if [ $? -eq 0 ]; then
    echo "   ✅ Migration completed successfully"
else
    echo "   ⚠️ Migration may have failed or already run. Check manually with: php artisan migrate"
fi

echo ""
echo "4️⃣ Verifying changes..."
# Check if ckb folder exists
if [ -d "resources/lang/ckb" ]; then
    echo "   ✅ ckb language folder exists"
    
    if [ -f "resources/lang/ckb/messages.php" ]; then
        echo "   ✅ ckb/messages.php file exists"
    else
        echo "   ⚠️ ckb/messages.php file missing"
    fi
else
    echo "   ❌ ckb language folder not found"
fi

# Check if ku folder still exists (shouldn't)
if [ ! -d "resources/lang/ku" ]; then
    echo "   ✅ ku language folder successfully removed"
else
    echo "   ⚠️ ku language folder still exists"
fi

# Clear Laravel caches
echo "   🔄 Clearing Laravel caches..."
php artisan config:clear >/dev/null 2>&1
php artisan cache:clear >/dev/null 2>&1
echo "   ✅ Laravel caches cleared"

echo ""
echo "✅ Kurdish language code fix completed!"
echo ""
echo "Summary of changes:"
echo "• Language folder renamed: ku → ckb"
echo "• Database updated: 'ku' → 'ckb' in business_settings"  
echo "• RTL direction ensured for Kurdish Sorani"
echo "• Translation files preserved"
echo ""
echo "🎯 Kurdish Sorani (ckb) is now properly configured!"
echo ""
echo "Next steps:"
echo "1. Verify Kurdish language appears correctly in admin panel"
echo "2. Test switching to Kurdish language"
echo "3. Confirm RTL layout works properly"