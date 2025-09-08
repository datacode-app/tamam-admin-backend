#!/bin/bash

# Environment Switcher Script for Tamam Admin Backend
# Usage: ./switch-env.sh [local|dev1|production]

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to display usage
show_usage() {
    echo -e "${BLUE}Environment Switcher for Tamam Admin Backend${NC}"
    echo ""
    echo "Usage: $0 [environment]"
    echo ""
    echo "Available environments:"
    echo "  local      - Local development with Docker MySQL"
    echo "  dev1       - Development server environment"  
    echo "  production - Production environment"
    echo ""
    echo "Current environment:"
    if [ -f .env ]; then
        current_env=$(grep "APP_ENV=" .env | cut -d'=' -f2)
        current_url=$(grep "APP_URL=" .env | cut -d'=' -f2)
        echo -e "  ${GREEN}APP_ENV: $current_env${NC}"
        echo -e "  ${GREEN}APP_URL: $current_url${NC}"
    else
        echo -e "  ${RED}No .env file found${NC}"
    fi
}

# Function to backup current .env
backup_env() {
    if [ -f .env ]; then
        cp .env .env.backup.$(date +%Y%m%d_%H%M%S)
        echo -e "${YELLOW}Current .env backed up${NC}"
    fi
}

# Function to switch environment
switch_environment() {
    local env=$1
    local env_file=".env.$env"
    
    if [ ! -f "$env_file" ]; then
        echo -e "${RED}Error: Environment file $env_file not found${NC}"
        exit 1
    fi
    
    echo -e "${BLUE}Switching to $env environment...${NC}"
    
    # Backup current .env
    backup_env
    
    # Copy new environment
    cp "$env_file" .env
    
    # Clear Laravel caches
    echo -e "${YELLOW}Clearing Laravel caches...${NC}"
    php artisan config:clear 2>/dev/null || true
    php artisan cache:clear 2>/dev/null || true
    php artisan route:clear 2>/dev/null || true
    php artisan view:clear 2>/dev/null || true
    
    # Show new configuration
    echo -e "${GREEN}✅ Switched to $env environment${NC}"
    echo ""
    echo "New configuration:"
    echo -e "  APP_ENV: $(grep "APP_ENV=" .env | cut -d'=' -f2)"
    echo -e "  APP_URL: $(grep "APP_URL=" .env | cut -d'=' -f2)"
    echo -e "  DB_HOST: $(grep "DB_HOST=" .env | cut -d'=' -f2)"
    echo -e "  DB_DATABASE: $(grep "DB_DATABASE=" .env | cut -d'=' -f2)"
    
    # Environment-specific instructions
    case $env in
        "local")
            echo ""
            echo -e "${YELLOW}📝 Local Development Notes:${NC}"
            echo "  • Make sure Docker MySQL is running"
            echo "  • Database: tamamdb, User: root, Password: root"
            echo "  • Run: docker-compose up -d mysql"
            ;;
        "production")
            echo ""
            echo -e "${RED}⚠️  PRODUCTION ENVIRONMENT ACTIVE${NC}"
            echo -e "${RED}   Remember to run production checklist before deployment!${NC}"
            echo -e "${YELLOW}   Run: ./production-checklist.sh${NC}"
            ;;
        "dev1")
            echo ""
            echo -e "${YELLOW}📝 Dev1 Environment Active${NC}"
            echo "  • Staging server environment"
            echo "  • Use for testing before production"
            ;;
    esac
}

# Main script logic
if [ $# -eq 0 ]; then
    show_usage
    exit 0
fi

case $1 in
    "local"|"dev1"|"production")
        switch_environment $1
        ;;
    "status"|"current")
        show_usage
        ;;
    "help"|"-h"|"--help")
        show_usage
        ;;
    *)
        echo -e "${RED}Error: Unknown environment '$1'${NC}"
        echo ""
        show_usage
        exit 1
        ;;
esac