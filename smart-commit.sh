#!/bin/bash

# 🚀 TAMAM Smart Commit Script
# Intelligent commit script with team member attribution and deployment awareness
# Author: Hooshyar <hooseyr@gmail.com>

set -e

# Color codes for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
PURPLE='\033[0;35m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

# Team member configuration
declare -A TEAM_MEMBERS=(
    ["hooshyar"]="Hooshyar <hooseyr@gmail.com>"
    ["datacode"]="Datacode-backend <ferbon.com@gmail.com>"
    ["backend"]="backend <backend@tamam.krd>"
)

# Commit type configuration with emojis
declare -A COMMIT_TYPES=(
    ["feat"]="✨ feat"
    ["fix"]="🔧 fix" 
    ["docs"]="📚 docs"
    ["style"]="💎 style"
    ["refactor"]="♻️ refactor"
    ["perf"]="⚡ perf"
    ["test"]="🧪 test"
    ["build"]="🏗️ build"
    ["ci"]="👷 ci"
    ["chore"]="🧹 chore"
    ["security"]="🔒 security"
    ["deploy"]="🚀 deploy"
    ["hotfix"]="🚨 hotfix"
    ["merge"]="🔀 merge"
    ["revert"]="⏪ revert"
    ["wip"]="🚧 wip"
    ["breaking"]="💥 breaking"
)

# Deployment configuration
declare -A DEPLOYMENT_INFO=(
    ["staging"]="🟡 staging.tamam.shop"
    ["prod"]="🔴 prod.tamam.shop"
    ["main"]="💻 development"
)

# Function to print colored output
print_color() {
    local color=$1
    shift
    echo -e "${color}$*${NC}"
}

# Function to print banner
print_banner() {
    print_color $CYAN "════════════════════════════════════════"
    print_color $CYAN "🚀 TAMAM Smart Commit Tool"
    print_color $CYAN "   Intelligent deployment-aware commits"
    print_color $CYAN "════════════════════════════════════════"
}

# Function to get current branch
get_current_branch() {
    git rev-parse --abbrev-ref HEAD
}

# Function to get deployment target
get_deployment_target() {
    local branch=$1
    case $branch in
        "staging") echo "🟡 staging.tamam.shop (Auto-deploy)" ;;
        "prod") echo "🔴 prod.tamam.shop (Auto-deploy)" ;;
        "main") echo "💻 Development branch (No auto-deploy)" ;;
        *) echo "🔵 Feature branch: $branch" ;;
    esac
}

# Function to detect team member from git config or prompt
detect_team_member() {
    local current_user=$(git config --get user.name | tr '[:upper:]' '[:lower:]' 2>/dev/null || echo "")
    local current_email=$(git config --get user.email 2>/dev/null || echo "")
    
    # Try to match current user with team members
    for key in "${!TEAM_MEMBERS[@]}"; do
        if [[ "$current_user" == *"$key"* ]] || [[ "$current_email" == *"$key"* ]]; then
            echo "$key"
            return
        fi
    done
    
    # If no match found, prompt user
    print_color $YELLOW "\n👥 Select team member:"
    local i=1
    local members=()
    for key in "${!TEAM_MEMBERS[@]}"; do
        echo "  $i) ${TEAM_MEMBERS[$key]}"
        members[$i]=$key
        ((i++))
    done
    
    read -p "Enter selection (1-${#TEAM_MEMBERS[@]}): " selection
    echo "${members[$selection]:-hooshyar}"
}

# Function to get commit type
get_commit_type() {
    print_color $YELLOW "\n📝 Select commit type:"
    local i=1
    local types=()
    for key in "${!COMMIT_TYPES[@]}"; do
        echo "  $i) ${COMMIT_TYPES[$key]} - $key"
        types[$i]=$key
        ((i++))
    done
    
    read -p "Enter selection (1-${#COMMIT_TYPES[@]}): " selection
    echo "${types[$selection]:-feat}"
}

# Function to get scope
get_scope() {
    local branch=$1
    local suggested_scopes=""
    
    case $branch in
        "staging") suggested_scopes="staging, admin, api, ui" ;;
        "prod") suggested_scopes="production, admin, hotfix, critical" ;;
        "main") suggested_scopes="admin, api, ui, core, auth" ;;
        *) suggested_scopes="feature, component, service, util" ;;
    esac
    
    print_color $YELLOW "\n🎯 Enter scope (e.g., $suggested_scopes):"
    read -p "Scope: " scope
    echo "${scope:-admin}"
}

# Function to get commit description
get_description() {
    print_color $YELLOW "\n📄 Enter commit description:"
    read -p "Description: " description
    echo "$description"
}

# Function to get additional details
get_details() {
    print_color $YELLOW "\n📋 Enter additional details (optional, press Enter to skip):"
    local details=""
    while IFS= read -r line; do
        [[ -z "$line" ]] && break
        details="${details}- $line\n"
    done
    echo -e "$details"
}

# Function to auto-detect co-authors based on file changes
detect_co_authors() {
    local current_user="$1"
    local co_authors=()
    
    # Get files that have been modified
    local modified_files=$(git diff --cached --name-only 2>/dev/null || git diff --name-only 2>/dev/null || echo "")
    
    if [[ -n "$modified_files" ]]; then
        # Check git blame for recent contributors to modified files
        while IFS= read -r file; do
            if [[ -f "$file" ]]; then
                local recent_authors=$(git log --pretty=format:"%an" -n 5 -- "$file" 2>/dev/null | sort | uniq)
                while IFS= read -r author; do
                    local author_key=$(echo "$author" | tr '[:upper:]' '[:lower:]')
                    for team_key in "${!TEAM_MEMBERS[@]}"; do
                        if [[ "$author_key" == *"$team_key"* ]] && [[ "$team_key" != "$current_user" ]]; then
                            if [[ ! " ${co_authors[@]} " =~ " ${TEAM_MEMBERS[$team_key]} " ]]; then
                                co_authors+=("${TEAM_MEMBERS[$team_key]}")
                            fi
                        fi
                    done
                done <<< "$recent_authors"
            fi
        done <<< "$modified_files"
    fi
    
    printf '%s\n' "${co_authors[@]}"
}

# Function to build commit message
build_commit_message() {
    local commit_type="$1"
    local scope="$2"
    local description="$3"
    local details="$4"
    local branch="$5"
    local primary_author="$6"
    local co_authors="$7"
    local deployment_target="$8"
    
    local emoji="${COMMIT_TYPES[$commit_type]}"
    local message=""
    
    # Build main commit line
    if [[ -n "$scope" ]]; then
        message="${emoji}(${scope}): ${description}"
    else
        message="${emoji}: ${description}"
    fi
    
    # Add details if provided
    if [[ -n "$details" ]]; then
        message="${message}\n\n${details}"
    fi
    
    # Add deployment information
    if [[ "$branch" == "staging" || "$branch" == "prod" ]]; then
        message="${message}\n🚀 Deployment Target: ${deployment_target}"
        message="${message}\n⚡ Auto-deploy: Enabled"
    fi
    
    # Add co-authors
    if [[ -n "$co_authors" ]]; then
        message="${message}\n"
        while IFS= read -r author; do
            [[ -n "$author" ]] && message="${message}\nCo-authored-by: ${author}"
        done <<< "$co_authors"
    fi
    
    echo -e "$message"
}

# Function to preview and confirm commit
preview_and_confirm() {
    local message="$1"
    local branch="$2"
    local deployment_target="$3"
    
    print_color $PURPLE "\n═══ COMMIT PREVIEW ═══"
    echo -e "$message"
    
    print_color $BLUE "\n═══ DEPLOYMENT INFO ═══"
    print_color $BLUE "Branch: $branch"
    print_color $BLUE "Target: $deployment_target"
    
    if [[ "$branch" == "staging" || "$branch" == "prod" ]]; then
        print_color $YELLOW "⚠️  This will trigger automatic deployment!"
    fi
    
    print_color $GREEN "\n✅ Proceed with commit? (y/n): "
    read -r confirm
    [[ "$confirm" =~ ^[Yy]$ ]]
}

# Main execution
main() {
    clear
    print_banner
    
    # Check if we're in a git repository
    if ! git rev-parse --git-dir > /dev/null 2>&1; then
        print_color $RED "❌ Error: Not in a git repository"
        exit 1
    fi
    
    # Get current branch and deployment info
    local current_branch=$(get_current_branch)
    local deployment_target=$(get_deployment_target "$current_branch")
    
    print_color $CYAN "\n📍 Current Branch: $current_branch"
    print_color $CYAN "🎯 Target: $deployment_target"
    
    # Check for changes
    if ! git diff --cached --quiet 2>/dev/null && ! git diff --quiet 2>/dev/null; then
        print_color $YELLOW "\n📋 Staging changes for commit..."
        git add -A
    fi
    
    if git diff --cached --quiet 2>/dev/null; then
        print_color $YELLOW "⚠️  No changes to commit"
        exit 0
    fi
    
    # Gather commit information
    local team_member=$(detect_team_member)
    local commit_type=$(get_commit_type)
    local scope=$(get_scope "$current_branch")
    local description=$(get_description)
    local details=$(get_details)
    
    # Auto-detect co-authors
    local co_authors=$(detect_co_authors "$team_member")
    
    # Build commit message
    local commit_message=$(build_commit_message "$commit_type" "$scope" "$description" "$details" "$current_branch" "$team_member" "$co_authors" "$deployment_target")
    
    # Preview and confirm
    if preview_and_confirm "$commit_message" "$current_branch" "$deployment_target"; then
        print_color $GREEN "\n🚀 Creating commit..."
        
        # Create the commit
        git commit -m "$commit_message"
        
        print_color $GREEN "✅ Commit created successfully!"
        
        # Ask about pushing
        print_color $YELLOW "\n📤 Push to remote? (y/n): "
        read -r push_confirm
        if [[ "$push_confirm" =~ ^[Yy]$ ]]; then
            print_color $GREEN "🚀 Pushing to origin/$current_branch..."
            git push origin "$current_branch"
            
            if [[ "$current_branch" == "staging" || "$current_branch" == "prod" ]]; then
                print_color $GREEN "🎉 Deployment triggered for $deployment_target"
                print_color $CYAN "🔗 Monitor deployment: https://github.com/datacode-app/tamam-admin-backend/actions"
            fi
        fi
    else
        print_color $RED "❌ Commit cancelled"
        exit 1
    fi
}

# Run main function
main "$@"