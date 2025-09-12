#!/bin/bash
# Smart Multi-User Git Commit System with AI Agent
# Automatically segregates commits by project type and assigns appropriate GitHub users

set -e

# Configuration
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "$SCRIPT_DIR/../.." && pwd)"
CONFIG_FILE="$SCRIPT_DIR/commit-config.json"
AI_AGENT_SCRIPT="$SCRIPT_DIR/ai-commit-agent.js"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
PURPLE='\033[0;35m'
NC='\033[0m' # No Color

# Logging function
log() {
    local level=$1
    shift
    local message="$@"
    case $level in
        "INFO") echo -e "${GREEN}[INFO]${NC} $message" ;;
        "WARN") echo -e "${YELLOW}[WARN]${NC} $message" ;;
        "ERROR") echo -e "${RED}[ERROR]${NC} $message" ;;
        "DEBUG") echo -e "${BLUE}[DEBUG]${NC} $message" ;;
        "AI") echo -e "${PURPLE}[AI-AGENT]${NC} $message" ;;
    esac
}

# Check if required tools are available
check_dependencies() {
    log "INFO" "Checking dependencies..."
    
    if ! command -v git &> /dev/null; then
        log "ERROR" "Git is not installed"
        exit 1
    fi
    
    if ! command -v node &> /dev/null; then
        log "WARN" "Node.js not found - AI agent features will be limited"
    fi
    
    if ! command -v jq &> /dev/null; then
        log "WARN" "jq not found - installing via npm as fallback"
        npm install -g jq 2>/dev/null || log "ERROR" "Could not install jq"
    fi
}

# Load configuration
load_config() {
    if [[ ! -f "$CONFIG_FILE" ]]; then
        log "ERROR" "Configuration file not found: $CONFIG_FILE"
        log "INFO" "Run './smart-commit.sh --setup' first"
        exit 1
    fi
    
    # Export config variables
    eval "$(jq -r '
        .users | to_entries[] | 
        "export USER_" + (.key | ascii_upcase) + "_NAME=\"" + .value.name + "\""
    ' "$CONFIG_FILE")"
    
    eval "$(jq -r '
        .users | to_entries[] | 
        "export USER_" + (.key | ascii_upcase) + "_EMAIL=\"" + .value.email + "\""
    ' "$CONFIG_FILE")"

    # Owner (always include) - optional in config, fallback to current git user
    local owner_name
    local owner_email
    owner_name=$(jq -r '.owner.name // empty' "$CONFIG_FILE" 2>/dev/null || echo "")
    owner_email=$(jq -r '.owner.email // empty' "$CONFIG_FILE" 2>/dev/null || echo "")
    if [[ -z "$owner_name" ]]; then
        owner_name=$(git config user.name 2>/dev/null || echo "")
    fi
    if [[ -z "$owner_email" ]]; then
        owner_email=$(git config user.email 2>/dev/null || echo "")
    fi
    export OWNER_NAME="$owner_name"
    export OWNER_EMAIL="$owner_email"
    
    log "INFO" "Configuration loaded successfully"
}

# Analyze changed files and categorize them
analyze_changes() {
    log "INFO" "Analyzing changed files..."
    
    # Get all changed files (staged and unstaged)
    local changed_files=$(git diff --name-only HEAD 2>/dev/null || git ls-files --modified --others --exclude-standard)
    
    if [[ -z "$changed_files" ]]; then
        log "WARN" "No changes detected"
        exit 0
    fi
    
    # Initialize variables for different project types
    files_frontend=""
    files_backend=""
    files_infrastructure=""
    files_mobile=""
    files_docs=""
    
    # Categorize files
    while IFS= read -r file; do
        categorize_file "$file"
    done <<< "$changed_files"
    
    # Export categorized files
    [[ -n "$files_frontend" ]] && export "FILES_FRONTEND"="$files_frontend" && log "DEBUG" "frontend files: $files_frontend"
    [[ -n "$files_backend" ]] && export "FILES_BACKEND"="$files_backend" && log "DEBUG" "backend files: $files_backend"
    [[ -n "$files_infrastructure" ]] && export "FILES_INFRASTRUCTURE"="$files_infrastructure" && log "DEBUG" "infrastructure files: $files_infrastructure"
    [[ -n "$files_mobile" ]] && export "FILES_MOBILE"="$files_mobile" && log "DEBUG" "mobile files: $files_mobile"
    [[ -n "$files_docs" ]] && export "FILES_DOCS"="$files_docs" && log "DEBUG" "docs files: $files_docs"
}

# Categorize individual files based on path and content
categorize_file() {
    local file="$1"
    
    case "$file" in
        # Infrastructure files
        deployment/infrastructure/* | terraform.tfvars | *.tf | docker-compose*.yml | Dockerfile | kubernetes/* | .github/workflows/* | scripts/git/* | scripts/*devops* | scripts/*deploy* | scripts/*infra*)
            files_infrastructure+="$file "
            ;;
        
        # Mobile app files
        */pubspec.yaml | */android/* | */ios/* | */lib/* | *.dart | */flutter_*)
            files_mobile+="$file "
            ;;
        
        # Frontend files
        */package.json | */next.config.* | */src/components/* | */src/pages/* | *.tsx | *.jsx | */public/* | */styles/* | *.css | *.scss)
            files_frontend+="$file "
            ;;
        
        # Backend files
        app/* | config/* | database/* | routes/* | resources/views/* | composer.json | artisan | *.php | Modules/*)
            files_backend+="$file "
            ;;
        
        # Documentation
        *.md | docs/* | README* | LICENSE | *.txt)
            files_docs+="$file "
            ;;
        
        # Default to backend for Laravel structure
        *)
            if [[ -f "composer.json" ]]; then
                files_backend+="$file "
            else
                files_frontend+="$file "
            fi
            ;;
    esac
}

# Set git user based on project type
set_git_user() {
    local project_type="$1"
    
    case "$project_type" in
        "frontend"|"mobile")
            git config user.name "$USER_FRONTEND_NAME"
            git config user.email "$USER_FRONTEND_EMAIL"
            log "INFO" "Set git user to: $USER_FRONTEND_NAME ($USER_FRONTEND_EMAIL)"
            ;;
        "backend")
            git config user.name "$USER_BACKEND_NAME"
            git config user.email "$USER_BACKEND_EMAIL"
            log "INFO" "Set git user to: $USER_BACKEND_NAME ($USER_BACKEND_EMAIL)"
            ;;
        "infrastructure")
            git config user.name "$USER_DEVOPS_NAME"
            git config user.email "$USER_DEVOPS_EMAIL"
            log "INFO" "Set git user to: $USER_DEVOPS_NAME ($USER_DEVOPS_EMAIL)"
            ;;
        *)
            # Default to backend for mixed changes
            git config user.name "$USER_BACKEND_NAME"
            git config user.email "$USER_BACKEND_EMAIL"
            log "WARN" "Unknown project type '$project_type', defaulting to backend user"
            ;;
    esac
}

# Generate AI-powered commit message
generate_ai_commit_message() {
    local project_type="$1"
    local files="$2"
    
    if [[ -f "$AI_AGENT_SCRIPT" ]] && command -v node &> /dev/null; then
        local ai_message=$(node "$AI_AGENT_SCRIPT" "$project_type" "$files" 2>/dev/null || echo "")
        
        if [[ -n "$ai_message" ]]; then
            echo "$ai_message"
            return 0
        fi
    fi
    
    # Fallback to conventional commit format
    local scope=""
    case "$project_type" in
        "frontend") scope="web" ;;
        "mobile") scope="app" ;;
        "backend") scope="api" ;;
        "infrastructure") scope="infra" ;;
        *) scope="core" ;;
    esac
    
    echo "feat($scope): update $project_type components"
}

# Generate combined commit message for single commit mode
generate_combined_commit_message() {
    local primary_type="$1"
    local all_files="$2"
    shift 2
    local contributors=("$@")
    
    # Use AI agent with primary type if available
    if [[ -f "$AI_AGENT_SCRIPT" ]] && command -v node &> /dev/null; then
        local ai_message=$(node "$AI_AGENT_SCRIPT" "$primary_type" "$all_files" 2>/dev/null || echo "")
        
        if [[ -n "$ai_message" ]]; then
            # Enhance AI message with contributor context
            if [[ ${#contributors[@]} -gt 1 ]]; then
                echo "$ai_message (multi-team: ${contributors[*]})"
            else
                echo "$ai_message"
            fi
            return 0
        fi
    fi
    
    # Fallback: Generate comprehensive message
    local scope=""
    local action="update"
    
    case "$primary_type" in
        "frontend") scope="web" ;;
        "mobile") scope="app" ;;
        "backend") scope="api" ;;
        "infrastructure") scope="deploy" ;;
        "docs") scope="docs" ;;
        *) scope="core" ;;
    esac
    
    # Determine action based on contributors
    if [[ ${#contributors[@]} -gt 2 ]]; then
        action="integrate"
    elif [[ ${#contributors[@]} -eq 1 ]]; then
        action="update"
    else
        action="enhance"
    fi
    
    # Count total files for context
    local file_count=$(echo "$all_files" | wc -w)
    local context=""
    [[ $file_count -gt 10 ]] && context=" ($file_count files)"
    
    echo "🚀 feat($scope): $action ${primary_type} with cross-team changes${context}"
}

# Generate co-author attribution for Git commits
generate_co_authors() {
    local contributors=("$@")
    local co_authors=""
    local primary_set=false
    
    # Load user info from config for co-author attribution
    for contributor in "${contributors[@]}"; do
        if [[ "$primary_set" == "false" ]]; then
            primary_set=true
            continue  # Skip primary author (already set via git config)
        fi
        
        local author_name=""
        local author_email=""
        
        case "$contributor" in
            "frontend"|"mobile")
                author_name="$USER_FRONTEND_NAME"
                author_email="$USER_FRONTEND_EMAIL"
                ;;
            "backend")
                author_name="$USER_BACKEND_NAME"
                author_email="$USER_BACKEND_EMAIL"
                ;;
            "infrastructure")
                author_name="$USER_DEVOPS_NAME"
                author_email="$USER_DEVOPS_EMAIL"
                ;;
            "docs")
                # Default to backend user for docs
                author_name="$USER_BACKEND_NAME"
                author_email="$USER_BACKEND_EMAIL"
                ;;
        esac
        
        if [[ -n "$author_name" && -n "$author_email" ]]; then
            co_authors+="Co-authored-by: $author_name <$author_email>"$'\n'
        fi
    done
    
    echo "$co_authors"
}

# Generate a Co-authored-by footer for the always-include owner
generate_owner_footer() {
    local primary_email
    primary_email="$(git config user.email 2>/dev/null || echo "")"
    if [[ -n "$OWNER_NAME" && -n "$OWNER_EMAIL" && "$OWNER_EMAIL" != "$primary_email" ]]; then
        echo "Co-authored-by: $OWNER_NAME <$OWNER_EMAIL>"
    fi
}

# Create and commit changes by project type
commit_by_project_type() {
    local project_type="$1"
    local files="$2"
    
    if [[ -z "$files" ]]; then
        return 0
    fi
    
    log "INFO" "Processing $project_type changes..."
    
    # Set appropriate git user
    set_git_user "$project_type"
    
    # Add files to staging
    echo "$files" | xargs git add
    
    # Generate commit message
    local commit_message=$(generate_ai_commit_message "$project_type" "$files")
    
    log "INFO" "Committing with message: $commit_message"
    
    # Create commit (append owner co-author if configured)
    local owner_footer
    owner_footer="$(generate_owner_footer)"
    if [[ -n "$owner_footer" ]]; then
        git commit -m "$commit_message" -m "$owner_footer"
    else
        git commit -m "$commit_message"
    fi
    
    log "INFO" "✅ Committed $project_type changes"
}

# Single commit workflow - preserves user attribution but creates one commit
single_commit_workflow() {
    log "INFO" "Starting smart single-commit workflow..."
    
    # Check for uncommitted changes
    if git diff --quiet && git diff --cached --quiet; then
        log "WARN" "No changes to commit"
        exit 0
    fi
    
    # Analyze and categorize changes
    analyze_changes
    
    # Determine primary contributor based on most significant changes
    local primary_type=""
    local primary_count=0
    local contributors=()
    
    # Count files by type to determine primary contributor
    if [[ -n "$FILES_BACKEND" ]]; then
        local backend_count=$(echo "$FILES_BACKEND" | wc -w)
        [[ $backend_count -gt $primary_count ]] && primary_type="backend" && primary_count=$backend_count
        contributors+=("backend")
    fi
    
    if [[ -n "$FILES_FRONTEND" ]]; then
        local frontend_count=$(echo "$FILES_FRONTEND" | wc -w)
        [[ $frontend_count -gt $primary_count ]] && primary_type="frontend" && primary_count=$frontend_count
        contributors+=("frontend")
    fi
    
    if [[ -n "$FILES_MOBILE" ]]; then
        local mobile_count=$(echo "$FILES_MOBILE" | wc -w)
        [[ $mobile_count -gt $primary_count ]] && primary_type="mobile" && primary_count=$mobile_count
        contributors+=("mobile")
    fi
    
    if [[ -n "$FILES_INFRASTRUCTURE" ]]; then
        local infra_count=$(echo "$FILES_INFRASTRUCTURE" | wc -w)
        [[ $infra_count -gt $primary_count ]] && primary_type="infrastructure" && primary_count=$infra_count
        contributors+=("infrastructure")
    fi
    
    if [[ -n "$FILES_DOCS" ]]; then
        local docs_count=$(echo "$FILES_DOCS" | wc -w)
        [[ $docs_count -gt $primary_count ]] && primary_type="docs" && primary_count=$docs_count
        contributors+=("docs")
    fi
    
    # Default to backend if no clear primary type
    [[ -z "$primary_type" ]] && primary_type="backend"
    
    log "INFO" "Primary contributor: $primary_type ($primary_count files)"
    log "INFO" "All contributors: ${contributors[*]}"
    
    # Set git user to primary contributor
    set_git_user "$primary_type"
    
    # Add all files
    local all_files="$FILES_INFRASTRUCTURE $FILES_BACKEND $FILES_FRONTEND $FILES_MOBILE $FILES_DOCS"
    echo "$all_files" | xargs git add
    
    # Generate comprehensive commit message
    local commit_message=$(generate_combined_commit_message "$primary_type" "$all_files" "${contributors[@]}")
    
    log "INFO" "Committing with message: $commit_message"
    
    # Create single commit with co-authors
    local co_authors=$(generate_co_authors "${contributors[@]}")
    # Append owner co-author if not already present and not the primary
    local owner_footer
    owner_footer="$(generate_owner_footer)"
    if [[ -n "$owner_footer" && "$co_authors" != *"$OWNER_EMAIL"* ]]; then
        co_authors+="$owner_footer"$'\n'
    fi
    if [[ -n "$co_authors" ]]; then
        git commit -m "$commit_message" -m "$co_authors"
    else
        git commit -m "$commit_message"
    fi
    
    log "INFO" "✅ Single commit created successfully!"
    
    # Show summary
    echo
    log "INFO" "Commit Summary:"
    git log --oneline -n 1 --show-signature
}

# Main commit workflow
main_commit_workflow() {
    log "INFO" "Starting smart multi-user commit workflow..."
    
    # Check for uncommitted changes
    if git diff --quiet && git diff --cached --quiet; then
        log "WARN" "No changes to commit"
        exit 0
    fi
    
    # Analyze and categorize changes
    analyze_changes
    
    # Process each category separately
    [[ -n "$FILES_INFRASTRUCTURE" ]] && commit_by_project_type "infrastructure" "$FILES_INFRASTRUCTURE"
    [[ -n "$FILES_BACKEND" ]] && commit_by_project_type "backend" "$FILES_BACKEND"
    [[ -n "$FILES_FRONTEND" ]] && commit_by_project_type "frontend" "$FILES_FRONTEND"
    [[ -n "$FILES_MOBILE" ]] && commit_by_project_type "mobile" "$FILES_MOBILE"
    [[ -n "$FILES_DOCS" ]] && commit_by_project_type "docs" "$FILES_DOCS"
    
    log "INFO" "🎉 All commits completed successfully!"
    
    # Show summary
    echo
    log "INFO" "Commit Summary:"
    git log --oneline -n 5 --graph --color=always
}

# Setup function to create initial configuration
setup_configuration() {
    log "INFO" "Setting up smart commit configuration..."
    
    # Create scripts directory if it doesn't exist
    mkdir -p "$(dirname "$CONFIG_FILE")"
    
    # Interactive setup
    echo
    echo "Please provide GitHub user information for each specialization:"
    echo
    
    # Frontend/Mobile user
    read -p "Frontend/Mobile GitHub username (e.g., datacode-front): " frontend_name
    read -p "Frontend/Mobile GitHub email: " frontend_email
    
    # Backend user
    read -p "Backend GitHub username (e.g., datacode-backend): " backend_name
    read -p "Backend GitHub email: " backend_email
    
    # DevOps user
    read -p "DevOps/Infrastructure GitHub username (e.g., datacode-devops): " devops_name
    read -p "DevOps/Infrastructure GitHub email: " devops_email
    
    echo
    echo "Always include co-author (your personal identity):"
    read -p "Owner name (always included as co-author): " owner_name
    read -p "Owner email: " owner_email
    
    # Create configuration file
    cat > "$CONFIG_FILE" << EOF
{
  "users": {
    "frontend": {
      "name": "$frontend_name",
      "email": "$frontend_email",
      "specialization": ["React", "Next.js", "Flutter", "Mobile", "UI/UX"]
    },
    "backend": {
      "name": "$backend_name", 
      "email": "$backend_email",
      "specialization": ["Laravel", "PHP", "APIs", "Database", "Server Logic"]
    },
    "devops": {
      "name": "$devops_name",
      "email": "$devops_email", 
      "specialization": ["Terraform", "Docker", "CI/CD", "Infrastructure", "Deployment"]
    }
  },
  "owner": {
    "name": "$owner_name",
    "email": "$owner_email"
  },
  "project_paths": {
    "frontend": [
      "*/package.json",
      "*/src/components/*", 
      "*/src/pages/*",
      "*.tsx",
      "*.jsx",
      "*/public/*",
      "*/styles/*"
    ],
    "mobile": [
      "*/pubspec.yaml",
      "*/android/*",
      "*/ios/*", 
      "*/lib/*",
      "*.dart"
    ],
    "backend": [
      "app/*",
      "config/*",
      "database/*", 
      "routes/*",
      "composer.json",
      "*.php",
      "Modules/*"
    ],
    "infrastructure": [
      "deployment/*",
      "*.tf",
      "terraform.tfvars",
      "docker-compose*.yml",
      "Dockerfile",
      ".github/workflows/*"
    ]
  },
  "ai_config": {
    "enabled": true,
    "commit_style": "conventional",
    "analyze_code_changes": true,
    "generate_detailed_messages": true
  }
}
EOF
    
    log "INFO" "Configuration saved to: $CONFIG_FILE"
    
    # Create AI agent script
    create_ai_agent_script
    
    log "INFO" "✅ Setup completed! You can now use './smart-commit.sh' to make intelligent commits"
}

# Create AI agent script for commit message generation
create_ai_agent_script() {
    cat > "$AI_AGENT_SCRIPT" << 'EOF'
#!/usr/bin/env node
/**
 * AI-Powered Commit Message Generator
 * Analyzes code changes and generates intelligent commit messages
 */

const fs = require('fs');
const path = require('path');
const { execSync } = require('child_process');

// Configuration
const PROJECT_TYPE = process.argv[2] || 'general';
const FILES = (process.argv[3] || '').split(' ').filter(f => f.trim());

// Conventional commit types with descriptions
const COMMIT_TYPES = {
    feat: 'A new feature',
    fix: 'A bug fix', 
    docs: 'Documentation only changes',
    style: 'Changes that do not affect the meaning of the code',
    refactor: 'A code change that neither fixes a bug nor adds a feature',
    perf: 'A code change that improves performance',
    test: 'Adding missing tests or correcting existing tests',
    chore: 'Changes to the build process or auxiliary tools',
    ci: 'Changes to CI configuration files and scripts',
    build: 'Changes that affect the build system or external dependencies'
};

// Project-specific scope mapping
const PROJECT_SCOPES = {
    frontend: ['web', 'ui', 'components', 'pages', 'styles'],
    mobile: ['app', 'flutter', 'ios', 'android', 'mobile'],
    backend: ['api', 'server', 'database', 'auth', 'models'],
    infrastructure: ['infra', 'deploy', 'docker', 'ci', 'terraform'],
    docs: ['docs', 'readme', 'guide']
};

// Analyze git diff to understand changes
function analyzeChanges() {
    try {
        const diffOutput = execSync('git diff --cached --stat', { encoding: 'utf-8' });
        const diffDetails = execSync('git diff --cached', { encoding: 'utf-8' });
        
        const stats = diffOutput.split('\n')
            .filter(line => line.includes('|'))
            .map(line => {
                const parts = line.split('|');
                const file = parts[0].trim();
                const changes = parts[1].trim();
                return { file, changes };
            });
        
        // Analyze types of changes
        const hasAdditions = diffDetails.includes('+') && !diffDetails.match(/^\+{3}/gm);
        const hasDeletions = diffDetails.includes('-') && !diffDetails.match(/^-{3}/gm);
        const hasNewFiles = diffDetails.includes('new file mode');
        const hasRenames = diffDetails.includes('rename from');
        const hasPermissionChanges = diffDetails.includes('old mode') && diffDetails.includes('new mode');
        
        return {
            stats,
            hasAdditions,
            hasDeletions, 
            hasNewFiles,
            hasRenames,
            hasPermissionChanges,
            totalFiles: stats.length
        };
        
    } catch (error) {
        return { stats: [], totalFiles: 0 };
    }
}

// Determine commit type based on changes
function determineCommitType(analysis, files) {
    // Check for new features
    if (analysis.hasNewFiles || files.some(f => f.includes('component') || f.includes('page'))) {
        return 'feat';
    }
    
    // Check for bug fixes
    if (files.some(f => f.includes('fix') || f.includes('bug')) || 
        (analysis.hasDeletions && analysis.hasAdditions)) {
        return 'fix';
    }
    
    // Check for documentation
    if (files.every(f => f.match(/\.(md|txt|rst)$/i))) {
        return 'docs';
    }
    
    // Check for style changes
    if (files.some(f => f.match(/\.(css|scss|sass|less|styl)$/i))) {
        return 'style';
    }
    
    // Check for tests
    if (files.some(f => f.includes('test') || f.includes('spec'))) {
        return 'test';
    }
    
    // Check for configuration changes
    if (files.some(f => f.match(/\.(json|yaml|yml|toml|ini|conf)$/i)) ||
        files.some(f => f.includes('config') || f.includes('docker') || f.includes('.env'))) {
        return 'chore';
    }
    
    // Default to refactor if mixed changes
    if (analysis.hasAdditions && analysis.hasDeletions && !analysis.hasNewFiles) {
        return 'refactor';
    }
    
    // Default to feat for new additions
    return analysis.hasAdditions ? 'feat' : 'chore';
}

// Determine scope based on project type and files
function determineScope(projectType, files) {
    const scopes = PROJECT_SCOPES[projectType] || ['core'];
    
    // Try to match file paths to appropriate scopes
    for (const file of files) {
        if (file.includes('component')) return scopes[2] || scopes[0];
        if (file.includes('page') || file.includes('route')) return scopes[3] || scopes[0];
        if (file.includes('style') || file.includes('css')) return scopes[4] || scopes[0];
        if (file.includes('model') || file.includes('database')) return scopes[4] || scopes[0];
        if (file.includes('api') || file.includes('controller')) return scopes[0];
        if (file.includes('test') || file.includes('spec')) return 'test';
        if (file.includes('config') || file.includes('env')) return 'config';
    }
    
    return scopes[0];
}

// Generate descriptive commit message
function generateDescription(commitType, scope, files, analysis) {
    const fileCount = analysis.totalFiles;
    
    // Generate context-aware descriptions
    const descriptions = {
        feat: [
            `add new ${scope} functionality`,
            `implement ${scope} features`,
            `introduce ${scope} enhancements`,
            `create new ${scope} module`
        ],
        fix: [
            `resolve ${scope} issues`,
            `fix ${scope} bugs and errors`,
            `correct ${scope} functionality`,
            `patch ${scope} problems`
        ],
        refactor: [
            `refactor ${scope} code structure`, 
            `improve ${scope} code organization`,
            `optimize ${scope} implementation`,
            `restructure ${scope} modules`
        ],
        style: [
            `update ${scope} styling`,
            `improve ${scope} visual design`,
            `enhance ${scope} appearance`,
            `modify ${scope} styles`
        ],
        docs: [
            `update ${scope} documentation`,
            `improve ${scope} guides`,
            `enhance ${scope} readme`,
            `add ${scope} documentation`
        ],
        chore: [
            `update ${scope} configuration`,
            `maintain ${scope} dependencies`,
            `improve ${scope} build process`,
            `update ${scope} tooling`
        ]
    };
    
    const options = descriptions[commitType] || [`update ${scope} components`];
    
    // Select description based on file count and type
    let description = options[Math.floor(Math.random() * options.length)];
    
    // Add file count context for larger changes
    if (fileCount > 5) {
        description += ` (${fileCount} files)`;
    } else if (fileCount === 1) {
        const fileName = files[0].split('/').pop().replace(/\.[^.]+$/, '');
        description = description.replace('components', fileName);
    }
    
    return description;
}

// Generate emoji based on commit type (optional)
function getCommitEmoji(commitType) {
    const emojis = {
        feat: '✨',
        fix: '🐛', 
        docs: '📝',
        style: '💄',
        refactor: '♻️',
        perf: '⚡',
        test: '✅',
        chore: '🔧',
        ci: '👷',
        build: '📦'
    };
    
    return emojis[commitType] || '🚀';
}

// Main function to generate commit message
function generateCommitMessage() {
    if (FILES.length === 0) {
        console.log('chore: update project files');
        return;
    }
    
    const analysis = analyzeChanges();
    const commitType = determineCommitType(analysis, FILES);
    const scope = determineScope(PROJECT_TYPE, FILES);
    const description = generateDescription(commitType, scope, FILES, analysis);
    const emoji = getCommitEmoji(commitType);
    
    // Generate conventional commit message
    const message = `${emoji} ${commitType}(${scope}): ${description}`;
    
    console.log(message);
}

// Execute if called directly
if (require.main === module) {
    generateCommitMessage();
}
EOF
    
    chmod +x "$AI_AGENT_SCRIPT"
    log "INFO" "AI agent script created: $AI_AGENT_SCRIPT"
}

# Command line argument processing
case "${1:-}" in
    "--setup" | "setup")
        setup_configuration
        exit 0
        ;;
    "--help" | "-h" | "help")
        echo "Smart Multi-User Git Commit System"
        echo
        echo "Usage: $0 [OPTIONS]"
        echo
        echo "Options:"
        echo "  --setup           Initialize configuration for multi-user commits"
        echo "  --single-commit   Create one commit with proper user attribution"
        echo "  --help            Show this help message"
        echo "  --status          Show current git status and configuration"
        echo
        echo "Examples:"
        echo "  $0 --setup         # First time setup"
        echo "  $0                 # Make intelligent multi-commits (default)"
        echo "  $0 --single-commit # Make single commit for deployment"
        echo
        echo "Single Commit Mode:"
        echo "  Perfect for deployment workflows - creates one commit but preserves"
        echo "  user attribution by setting primary author and adding co-authors."
        echo
        exit 0
        ;;
    "--status" | "status")
        echo "Git Status:"
        git status --short
        echo
        if [[ -f "$CONFIG_FILE" ]]; then
            echo "Configuration:"
            jq '.' "$CONFIG_FILE"
        else
            echo "No configuration found. Run '$0 --setup' first."
        fi
        exit 0
        ;;
    "--single-commit" | "single")
        # Single commit workflow
        SINGLE_COMMIT_MODE=true
        ;;
    "")
        # Main workflow - no arguments
        ;;
    *)
        log "ERROR" "Unknown option: $1"
        echo "Use '$0 --help' for usage information"
        exit 1
        ;;
esac

# Main execution
check_dependencies
load_config

# Choose workflow based on mode
if [[ "${SINGLE_COMMIT_MODE:-false}" == "true" ]]; then
    single_commit_workflow
else
    main_commit_workflow
fi