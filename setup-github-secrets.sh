#!/bin/bash

# 🔐 GitHub Secrets Setup Script for TAMAM Staging Deployment
# Uses GitHub CLI to automatically configure deployment secrets

set -e  # Exit on any error

echo "🔐 Setting up GitHub Secrets for Automated Deployment"
echo "===================================================="
echo ""

# Check if gh CLI is installed
if ! command -v gh &> /dev/null; then
    echo "❌ GitHub CLI (gh) is not installed."
    echo "📥 Install it with: brew install gh"
    echo "   Or visit: https://cli.github.com/"
    exit 1
fi

# Check if user is authenticated
if ! gh auth status &> /dev/null; then
    echo "🔐 Not authenticated with GitHub CLI."
    echo "🔑 Please authenticate first:"
    echo "   gh auth login"
    exit 1
fi

echo "✅ GitHub CLI is installed and authenticated"
echo ""

# Configuration
SSH_KEY_PATH="$HOME/.ssh/tamam_staging_key"
SERVER_IP="46.101.190.171"

# Verify SSH key exists
if [[ ! -f "$SSH_KEY_PATH" ]]; then
    echo "❌ SSH key not found at: $SSH_KEY_PATH"
    echo "🔍 Available SSH keys:"
    ls -la ~/.ssh/*.key 2>/dev/null || echo "  No SSH keys found"
    echo ""
    echo "💡 Make sure the staging SSH key is at: $SSH_KEY_PATH"
    exit 1
fi

echo "✅ Found SSH key at: $SSH_KEY_PATH"
echo ""

# Get repository info
REPO_INFO=$(gh repo view --json owner,name)
REPO_OWNER=$(echo "$REPO_INFO" | jq -r '.owner.login')
REPO_NAME=$(echo "$REPO_INFO" | jq -r '.name')

echo "🏗️  Repository: $REPO_OWNER/$REPO_NAME"
echo ""

# Set up secrets
echo "🔑 Setting up GitHub secrets..."
echo ""

# 1. STAGING_SSH_KEY
echo "📝 Adding STAGING_SSH_KEY secret..."
if gh secret set STAGING_SSH_KEY < "$SSH_KEY_PATH"; then
    echo "✅ STAGING_SSH_KEY secret added successfully"
else
    echo "❌ Failed to add STAGING_SSH_KEY secret"
    exit 1
fi

# 2. STAGING_SERVER_IP
echo "📝 Adding STAGING_SERVER_IP secret..."
if echo "$SERVER_IP" | gh secret set STAGING_SERVER_IP; then
    echo "✅ STAGING_SERVER_IP secret added successfully"
else
    echo "❌ Failed to add STAGING_SERVER_IP secret"
    exit 1
fi

echo ""
echo "🎉 GitHub Secrets Setup Complete!"
echo "================================="
echo ""
echo "📊 Summary:"
echo "  ✅ STAGING_SSH_KEY: Set from $SSH_KEY_PATH"
echo "  ✅ STAGING_SERVER_IP: Set to $SERVER_IP"
echo ""
echo "🔍 Verify secrets (optional):"
echo "  gh secret list"
echo ""
echo "🚀 Next Steps:"
echo "  1. Commit and push your changes to main branch"
echo "  2. Go to repository Actions tab to monitor deployment"
echo "  3. Check deployment at: https://staging.tamam.shop/admin"
echo ""
echo "💡 Deployment will trigger automatically on every push to main branch!"