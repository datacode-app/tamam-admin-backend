#!/bin/bash

# 🚀 TAMAM Quick Commit Script
# Fast commit with team member detection
# Usage: ./quick-commit.sh "feat(admin): add new feature"

set -e

# Team member configuration (using regular arrays for compatibility)
TEAM_KEYS=("hooshyar" "datacode" "backend")
TEAM_VALUES=("Hooshyar <hooseyr@gmail.com>" "Datacode-backend <ferbon.com@gmail.com>" "backend <backend@tamam.krd>")

# Color codes
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
NC='\033[0m'

# Function to detect team member
detect_team_member() {
    local current_user=$(git config --get user.name | tr '[:upper:]' '[:lower:]' 2>/dev/null || echo "")
    local current_email=$(git config --get user.email 2>/dev/null || echo "")
    
    for i in "${!TEAM_KEYS[@]}"; do
        local key="${TEAM_KEYS[$i]}"
        if [[ "$current_user" == *"$key"* ]] || [[ "$current_email" == *"$key"* ]]; then
            echo "$key"
            return
        fi
    done
    echo "hooshyar"  # Default
}

# Function to get team member value by key
get_team_member() {
    local key="$1"
    for i in "${!TEAM_KEYS[@]}"; do
        if [[ "${TEAM_KEYS[$i]}" == "$key" ]]; then
            echo "${TEAM_VALUES[$i]}"
            return
        fi
    done
    echo "Hooshyar <hooseyr@gmail.com>"  # Default
}

# Function to auto-detect co-authors
detect_co_authors() {
    local current_user="$1"
    local co_authors=()
    
    # Get recently modified files and their contributors
    local modified_files=$(git diff --cached --name-only 2>/dev/null || git diff --name-only 2>/dev/null || echo "")
    
    if [[ -n "$modified_files" ]]; then
        while IFS= read -r file; do
            if [[ -f "$file" ]]; then
                local recent_authors=$(git log --pretty=format:"%an" -n 3 -- "$file" 2>/dev/null | sort | uniq)
                while IFS= read -r author; do
                    local author_key=$(echo "$author" | tr '[:upper:]' '[:lower:]')
                    for i in "${!TEAM_KEYS[@]}"; do
                        local team_key="${TEAM_KEYS[$i]}"
                        local team_value="${TEAM_VALUES[$i]}"
                        if [[ "$author_key" == *"$team_key"* ]] && [[ "$team_key" != "$current_user" ]]; then
                            if [[ ! " ${co_authors[@]} " =~ " $team_value " ]]; then
                                co_authors+=("$team_value")
                            fi
                        fi
                    done
                done <<< "$recent_authors"
            fi
        done <<< "$modified_files"
    fi
    
    printf '%s\n' "${co_authors[@]}"
}

# Main function
main() {
    local commit_msg="$1"
    
    if [[ -z "$commit_msg" ]]; then
        echo -e "${YELLOW}Usage: $0 \"commit message\"${NC}"
        echo -e "${YELLOW}Example: $0 \"feat(admin): add user management\"${NC}"
        exit 1
    fi
    
    # Check git repo
    if ! git rev-parse --git-dir > /dev/null 2>&1; then
        echo -e "${RED}❌ Error: Not in a git repository${NC}"
        exit 1
    fi
    
    # Stage changes
    git add -A
    
    # Check for changes
    if git diff --cached --quiet 2>/dev/null; then
        echo -e "${YELLOW}⚠️  No changes to commit${NC}"
        exit 0
    fi
    
    # Get current branch and deployment info
    local current_branch=$(git rev-parse --abbrev-ref HEAD)
    local team_member=$(detect_team_member)
    local co_authors=$(detect_co_authors "$team_member")
    
    # Build commit message
    local full_message="$commit_msg"
    
    # Add deployment info for deployment branches
    if [[ "$current_branch" == "staging" ]]; then
        full_message="${full_message}\n\n🚀 Deployment Target: 🟡 staging.tamam.shop\n⚡ Auto-deploy: Enabled"
    elif [[ "$current_branch" == "prod" ]]; then
        full_message="${full_message}\n\n🚀 Deployment Target: 🔴 prod.tamam.shop\n⚡ Auto-deploy: Enabled"
    fi
    
    # Add co-authors
    if [[ -n "$co_authors" ]]; then
        full_message="${full_message}\n"
        while IFS= read -r author; do
            [[ -n "$author" ]] && full_message="${full_message}\nCo-authored-by: ${author}"
        done <<< "$co_authors"
    fi
    
    # Commit
    echo -e "${GREEN}🚀 Creating commit on branch: $current_branch${NC}"
    git commit -m "$(echo -e "$full_message")"
    
    echo -e "${GREEN}✅ Commit created successfully!${NC}"
    
    # Auto-push for deployment branches
    if [[ "$current_branch" == "staging" || "$current_branch" == "prod" ]]; then
        echo -e "${YELLOW}🚀 Auto-pushing deployment branch...${NC}"
        git push origin "$current_branch"
        echo -e "${GREEN}🎉 Deployment triggered!${NC}"
        echo -e "${BLUE}🔗 Monitor: https://github.com/datacode-app/tamam-admin-backend/actions${NC}"
    else
        echo -e "${YELLOW}📤 Push manually: git push origin $current_branch${NC}"
    fi
}

main "$@"