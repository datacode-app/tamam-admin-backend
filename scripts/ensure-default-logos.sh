#!/bin/bash

# Ensure Default Tamam Logos Script
# This script ensures that the default Tamam logos are always available
# Run this during deployment or server setup

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(dirname "$SCRIPT_DIR")"
STORAGE_DIR="$PROJECT_ROOT/storage/app/public/business"
PUBLIC_ASSETS_DIR="$PROJECT_ROOT/public/assets/admin/img"

echo "🎨 Ensuring Tamam default logos are in place..."

# Create directories if they don't exist
mkdir -p "$STORAGE_DIR"
mkdir -p "$PUBLIC_ASSETS_DIR"

# Default logo files (base64 encoded or copy from assets)
LOGO_SOURCE="$PROJECT_ROOT/../assets/logo.png"
ICON_SOURCE="$PROJECT_ROOT/../assets/fav.png"

# Check if source files exist
if [[ -f "$LOGO_SOURCE" ]]; then
    echo "📋 Copying default logo from assets..."
    cp "$LOGO_SOURCE" "$STORAGE_DIR/tamam-default-logo.png"
    cp "$LOGO_SOURCE" "$PUBLIC_ASSETS_DIR/logo.png"
    cp "$LOGO_SOURCE" "$PUBLIC_ASSETS_DIR/invoice/logo.png"
    echo "✅ Default logo installed"
else
    echo "⚠️  Source logo not found at $LOGO_SOURCE"
fi

if [[ -f "$ICON_SOURCE" ]]; then
    echo "📋 Copying default icon from assets..."
    cp "$ICON_SOURCE" "$STORAGE_DIR/tamam-default-icon.png"
    cp "$ICON_SOURCE" "$PUBLIC_ASSETS_DIR/favicon.png"
    echo "✅ Default icon installed"
else
    echo "⚠️  Source icon not found at $ICON_SOURCE"
fi

# Set proper permissions
chmod 644 "$STORAGE_DIR"/*.png 2>/dev/null || true
chmod 644 "$PUBLIC_ASSETS_DIR"/*.png 2>/dev/null || true

# Create storage link if it doesn't exist
if [[ ! -L "$PROJECT_ROOT/public/storage" ]]; then
    echo "🔗 Creating storage link..."
    cd "$PROJECT_ROOT"
    php artisan storage:link || true
fi

echo "🎯 Default Tamam logos are now in place!"
echo "   - Storage: $STORAGE_DIR/tamam-default-logo.png"
echo "   - Storage: $STORAGE_DIR/tamam-default-icon.png"
echo "   - Public: $PUBLIC_ASSETS_DIR/logo.png"
echo "   - Public: $PUBLIC_ASSETS_DIR/favicon.png"
