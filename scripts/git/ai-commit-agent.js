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
    
    // Generate conventional commit message (clean format)
    const message = `${emoji} ${commitType}(${scope}): ${description}`;
    
    // Output only the clean message
    console.log(message);
}

// Execute if called directly
if (require.main === module) {
    generateCommitMessage();
}
