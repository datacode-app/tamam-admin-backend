#!/bin/bash

# 🔐 Production GitHub Secrets Setup Script for TAMAM
# Uses GitHub CLI to automatically configure production deployment secrets

set -e  # Exit on any error

echo "🔐 Setting up Production GitHub Secrets"
echo "======================================"
echo "⚠️  PRODUCTION ENVIRONMENT SETUP ⚠️"
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
SSH_KEY_PATH="$HOME/.ssh/tamam_production_key"
SERVER_IP="134.209.230.97"

# Verify SSH key exists
if [[ ! -f "$SSH_KEY_PATH" ]]; then
    echo "❌ Production SSH key not found at: $SSH_KEY_PATH"
    echo "🔍 Available SSH keys:"
    ls -la ~/.ssh/*.key 2>/dev/null || echo "  No SSH keys found"
    echo ""
    echo "💡 You need to:"
    echo "   1. Generate a new SSH key for production: ssh-keygen -t rsa -b 4096 -f $SSH_KEY_PATH"
    echo "   2. Add the public key to the production server"
    echo "   3. Test SSH connection: ssh -i $SSH_KEY_PATH root@$SERVER_IP"
    exit 1
fi

echo "✅ Found production SSH key at: $SSH_KEY_PATH"
echo ""

# Get repository info
REPO_INFO=$(gh repo view --json owner,name)
REPO_OWNER=$(echo "$REPO_INFO" | jq -r '.owner.login')
REPO_NAME=$(echo "$REPO_INFO" | jq -r '.name')

echo "🏗️  Repository: $REPO_OWNER/$REPO_NAME"
echo ""

# Set up production secrets
echo "🔑 Setting up Production GitHub secrets..."
echo ""

# 1. PRODUCTION_SSH_KEY
echo "📝 Adding PRODUCTION_SSH_KEY secret..."
if gh secret set PRODUCTION_SSH_KEY < "$SSH_KEY_PATH"; then
    echo "✅ PRODUCTION_SSH_KEY secret added successfully"
else
    echo "❌ Failed to add PRODUCTION_SSH_KEY secret"
    exit 1
fi

# 2. PRODUCTION_SERVER_IP
echo "📝 Adding PRODUCTION_SERVER_IP secret..."
if echo "$SERVER_IP" | gh secret set PRODUCTION_SERVER_IP; then
    echo "✅ PRODUCTION_SERVER_IP secret added successfully"
else
    echo "❌ Failed to add PRODUCTION_SERVER_IP secret"
    exit 1
fi

echo ""
echo "🎉 Production GitHub Secrets Setup Complete!"
echo "============================================"
echo ""
echo "📊 Summary:"
echo "  ✅ PRODUCTION_SSH_KEY: Set from $SSH_KEY_PATH"
echo "  ✅ PRODUCTION_SERVER_IP: Set to $SERVER_IP (prod.tamam.shop)"
echo ""
echo "🔍 Verify secrets (optional):"
echo "  gh secret list"
echo ""
echo "🚀 Next Steps:"
echo "  1. Create and switch to 'prod' branch"
echo "  2. Commit your production-ready code"
echo "  3. Push to 'prod' branch to trigger deployment"
echo "  4. Monitor deployment in GitHub Actions tab"
echo "  5. Test deployment at: https://prod.tamam.shop/admin"
echo ""
echo "⚠️  CRITICAL REMINDERS:"
echo "  • Production deployments are LIVE and affect real users"
echo "  • Always test thoroughly in staging before deploying to prod"
echo "  • Monitor the application closely after deployment"
echo "  • Have a rollback plan ready if issues occur"
echo ""