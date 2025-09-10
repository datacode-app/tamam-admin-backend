#!/bin/bash

# 🔧 Production Server Access Setup
# This script adds the production SSH key to the server

SERVER_IP="134.209.230.97"
PRODUCTION_KEY_PATH="$HOME/.ssh/tamam_production_key.pub"

echo "🔧 Setting up production server access..."
echo "Server: $SERVER_IP"
echo ""

# Check if production key exists
if [[ ! -f "$PRODUCTION_KEY_PATH" ]]; then
    echo "❌ Production SSH key not found at: $PRODUCTION_KEY_PATH"
    exit 1
fi

echo "✅ Found production SSH key"

# Try different existing keys to access the server
KEYS=("tamam_deployment_rsa" "tamam_deploy" "id_rsa")
CONNECTED=false

for key in "${KEYS[@]}"; do
    KEY_PATH="$HOME/.ssh/$key"
    if [[ -f "$KEY_PATH" ]]; then
        echo "🔑 Trying key: $key"
        if ssh -i "$KEY_PATH" -o ConnectTimeout=10 -o StrictHostKeyChecking=no root@$SERVER_IP "whoami" >/dev/null 2>&1; then
            echo "✅ Connected with $key"
            
            # Add production key
            echo "📝 Adding production SSH key to server..."
            cat "$PRODUCTION_KEY_PATH" | ssh -i "$KEY_PATH" root@$SERVER_IP "mkdir -p ~/.ssh && cat >> ~/.ssh/authorized_keys && chmod 700 ~/.ssh && chmod 600 ~/.ssh/authorized_keys"
            
            echo "✅ Production SSH key added successfully"
            CONNECTED=true
            break
        fi
    fi
done

if [[ "$CONNECTED" == "false" ]]; then
    echo "❌ Could not connect to server with any existing keys"
    echo "💡 You may need to add the production key manually via DigitalOcean console"
    echo ""
    echo "Production SSH Key to add:"
    cat "$PRODUCTION_KEY_PATH"
    exit 1
fi

# Test production key connection
echo ""
echo "🧪 Testing production key connection..."
if ssh -i "${PRODUCTION_KEY_PATH%.*}" -o ConnectTimeout=10 root@$SERVER_IP "echo 'Production SSH key working!'" >/dev/null 2>&1; then
    echo "✅ Production SSH key is working!"
else
    echo "⚠️  Production SSH key connection test failed"
fi

echo ""
echo "🎉 Production server access setup complete!"