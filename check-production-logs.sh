#!/bin/bash

# 🔍 Production Server Log Monitoring Script
# Based on production deployment workflow configuration
# Usage: ./check-production-logs.sh [log-type] [lines]

set -e  # Exit on any error

# Configuration - Get from GitHub secrets or configure manually
PRODUCTION_SSH_KEY="~/.ssh/tamam_production_key"
PRODUCTION_SERVER_IP="134.209.230.97"  # Production server IP
REMOTE_PATH="/var/www/tamam"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${BLUE}[PROD-LOGS]${NC} $1"
}

print_success() {
    echo -e "${GREEN}✅ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

print_error() {
    echo -e "${RED}❌ $1${NC}"
}

# Get production server IP from GitHub secrets or use configured IP
get_production_server_ip() {
    if [ -n "$PRODUCTION_SERVER_IP" ]; then
        print_success "Using configured production server IP: $PRODUCTION_SERVER_IP"
        return 0
    fi
    
    if command -v gh >/dev/null 2>&1; then
        PRODUCTION_SERVER_IP=$(gh secret view PRODUCTION_SERVER_IP 2>/dev/null || echo "")
        if [ -n "$PRODUCTION_SERVER_IP" ]; then
            print_success "Retrieved production server IP from GitHub secrets"
        else
            print_warning "Could not retrieve server IP from GitHub secrets"
            echo "Please set PRODUCTION_SERVER_IP manually in this script"
            exit 1
        fi
    else
        print_error "GitHub CLI (gh) not found. Please install it or set PRODUCTION_SERVER_IP manually"
        exit 1
    fi
}

# Test SSH connection
test_ssh_connection() {
    print_status "Testing SSH connection to production server..."
    if ssh -i "$PRODUCTION_SSH_KEY" -o ConnectTimeout=10 root@"$PRODUCTION_SERVER_IP" "echo 'SSH connection successful'" > /dev/null 2>&1; then
        print_success "SSH connection to production server verified"
    else
        print_error "SSH connection failed. Please check SSH key and server status"
        print_error "Key: $PRODUCTION_SSH_KEY"
        print_error "Server: $PRODUCTION_SERVER_IP"
        exit 1
    fi
}

# Show available log types
show_log_types() {
    echo ""
    echo "📋 Available Log Types:"
    echo "  laravel     - Laravel application logs"
    echo "  nginx       - Nginx access and error logs" 
    echo "  php         - PHP-FPM error logs"
    echo "  system      - System logs (syslog)"
    echo "  all         - Show all logs"
    echo ""
    echo "📖 Usage Examples:"
    echo "  ./check-production-logs.sh laravel 50    # Last 50 lines of Laravel logs"
    echo "  ./check-production-logs.sh nginx         # Last 20 lines of Nginx logs"
    echo "  ./check-production-logs.sh all 100       # Last 100 lines of all logs"
    echo ""
}

# Check Laravel logs
check_laravel_logs() {
    local lines=${1:-20}
    print_status "Checking Laravel logs (last $lines lines)..."
    echo ""
    
    ssh -i "$PRODUCTION_SSH_KEY" root@"$PRODUCTION_SERVER_IP" "
        echo '=== LARAVEL APPLICATION LOGS ==='
        if [ -f '$REMOTE_PATH/storage/logs/laravel.log' ]; then
            tail -n $lines '$REMOTE_PATH/storage/logs/laravel.log'
        else
            echo 'No Laravel log file found at $REMOTE_PATH/storage/logs/laravel.log'
        fi
    "
}

# Check Nginx logs
check_nginx_logs() {
    local lines=${1:-20}
    print_status "Checking Nginx logs (last $lines lines)..."
    echo ""
    
    ssh -i "$PRODUCTION_SSH_KEY" root@"$PRODUCTION_SERVER_IP" "
        echo '=== NGINX ERROR LOGS ==='
        if [ -f '/var/log/nginx/error.log' ]; then
            tail -n $lines /var/log/nginx/error.log
        else
            echo 'No Nginx error log found'
        fi
        
        echo ''
        echo '=== NGINX ACCESS LOGS ==='
        if [ -f '/var/log/nginx/access.log' ]; then
            tail -n $lines /var/log/nginx/access.log
        else
            echo 'No Nginx access log found'
        fi
    "
}

# Check PHP logs
check_php_logs() {
    local lines=${1:-20}
    print_status "Checking PHP-FPM logs (last $lines lines)..."
    echo ""
    
    ssh -i "$PRODUCTION_SSH_KEY" root@"$PRODUCTION_SERVER_IP" "
        echo '=== PHP-FPM ERROR LOGS ==='
        if [ -f '/var/log/php8.3-fpm.log' ]; then
            tail -n $lines /var/log/php8.3-fpm.log
        elif [ -f '/var/log/php-fpm.log' ]; then
            tail -n $lines /var/log/php-fpm.log
        else
            echo 'No PHP-FPM log found'
        fi
    "
}

# Check system logs
check_system_logs() {
    local lines=${1:-20}
    print_status "Checking system logs (last $lines lines)..."
    echo ""
    
    ssh -i "$PRODUCTION_SSH_KEY" root@"$PRODUCTION_SERVER_IP" "
        echo '=== SYSTEM LOGS (SYSLOG) ==='
        if [ -f '/var/log/syslog' ]; then
            tail -n $lines /var/log/syslog
        else
            echo 'No syslog found'
        fi
    "
}

# Check all logs
check_all_logs() {
    local lines=${1:-20}
    check_laravel_logs "$lines"
    echo ""
    check_nginx_logs "$lines"
    echo ""
    check_php_logs "$lines"
    echo ""
    check_system_logs "$lines"
}

# Monitor logs in real-time
monitor_logs() {
    local log_type=${1:-laravel}
    print_status "Starting real-time log monitoring for: $log_type"
    print_status "Press Ctrl+C to stop monitoring"
    echo ""
    
    case $log_type in
        laravel)
            ssh -i "$PRODUCTION_SSH_KEY" root@"$PRODUCTION_SERVER_IP" "tail -f '$REMOTE_PATH/storage/logs/laravel.log'"
            ;;
        nginx)
            ssh -i "$PRODUCTION_SSH_KEY" root@"$PRODUCTION_SERVER_IP" "tail -f /var/log/nginx/error.log"
            ;;
        php)
            ssh -i "$PRODUCTION_SSH_KEY" root@"$PRODUCTION_SERVER_IP" "tail -f /var/log/php8.3-fpm.log || tail -f /var/log/php-fpm.log"
            ;;
        *)
            print_error "Invalid log type for monitoring: $log_type"
            echo "Available types: laravel, nginx, php"
            exit 1
            ;;
    esac
}

# Main script logic
main() {
    local log_type=${1:-help}
    local lines=${2:-20}
    
    print_status "🚀 Production Server Log Monitor"
    print_status "Target: prod.tamam.shop"
    print_status "⏰ Started at: $(date)"
    echo ""
    
    # Handle help command
    if [ "$log_type" = "help" ] || [ "$log_type" = "--help" ] || [ "$log_type" = "-h" ]; then
        show_log_types
        exit 0
    fi
    
    # Handle monitor command
    if [ "$log_type" = "monitor" ]; then
        get_production_server_ip
        test_ssh_connection
        monitor_logs "$lines"  # In this case, $lines is actually the log type
        exit 0
    fi
    
    # Get server IP and test connection
    get_production_server_ip
    test_ssh_connection
    
    # Execute log checking based on type
    case $log_type in
        laravel)
            check_laravel_logs "$lines"
            ;;
        nginx)
            check_nginx_logs "$lines"
            ;;
        php)
            check_php_logs "$lines"
            ;;
        system)
            check_system_logs "$lines"
            ;;
        all)
            check_all_logs "$lines"
            ;;
        *)
            print_error "Unknown log type: $log_type"
            show_log_types
            exit 1
            ;;
    esac
    
    echo ""
    print_success "Log check completed!"
    print_status "For real-time monitoring, use: ./check-production-logs.sh monitor [log-type]"
}

# Run main function with all arguments
main "$@"