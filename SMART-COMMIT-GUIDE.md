# 🚀 Smart Commit Scripts Guide

Intelligent commit tools with automatic team member detection and deployment awareness for the TAMAM platform.

## 📁 Available Scripts

### 1. `smart-commit.sh` - Interactive Full-Featured Commit Tool
**Interactive guided commit creation with all features**

### 2. `quick-commit.sh` - Fast Command-Line Commit Tool  
**One-line commits with automatic enhancements**

---

## 🎯 Features

### ✨ Automatic Team Member Detection
- **Detects current user** from git configuration
- **Auto-identifies team members**: Hooshyar, Datacode-backend, backend
- **Smart co-author detection** based on file modification history
- **No Claude attribution** - only real team members

### 🚀 Deployment-Aware Commits
- **Branch detection**: Automatically detects current branch
- **Deployment warnings**: Alerts when commits will trigger deployments
- **Auto-push**: Automatically pushes deployment branches (`staging`, `prod`)
- **Deployment tracking**: Provides GitHub Actions monitoring links

### 👥 Team Collaboration
- **Co-author detection**: Automatically finds contributors to modified files
- **Proper attribution**: Ensures team members get credit for their work
- **Consistent format**: Standardized commit message structure

---

## 🛠 Usage

### Quick Commit (Recommended for Fast Workflow)

```bash
# Basic usage
./quick-commit.sh "feat(admin): add user management"

# Examples for different commit types
./quick-commit.sh "🔧 fix(api): resolve authentication bug"
./quick-commit.sh "📚 docs(readme): update installation guide"
./quick-commit.sh "⚡ perf(admin): optimize dashboard loading"
./quick-commit.sh "🧪 test(auth): add login integration tests"
./quick-commit.sh "🚀 deploy(prod): release version 2.1.0"
```

### Interactive Commit (Full Control)

```bash
# Launch interactive mode
./smart-commit.sh

# Follow the guided prompts:
# 1. Select commit type (feat, fix, docs, etc.)
# 2. Enter scope (admin, api, ui, etc.)  
# 3. Enter description
# 4. Add additional details
# 5. Review and confirm
```

---

## 🎨 Commit Message Format

### Structure
```
emoji type(scope): description

Additional details
- Detail line 1
- Detail line 2

🚀 Deployment Target: target-server (if applicable)
⚡ Auto-deploy: Enabled (if applicable)

Co-authored-by: Team Member <email>
```

### Example Output
```
🧪 test(scripts): add smart commit functionality

🚀 Deployment Target: 🔴 prod.tamam.shop
⚡ Auto-deploy: Enabled

Co-authored-by: Datacode-backend <ferbon.com@gmail.com>
```

---

## 🌟 Smart Features

### 🔍 Automatic Co-Author Detection
The scripts analyze:
- **Modified files** in the current commit
- **Recent contributors** to those files (last 3 commits)
- **Team member matching** based on author names
- **Automatic attribution** without duplicates

### 🚀 Deployment Intelligence

| Branch | Behavior | Target |
|--------|----------|--------|
| `staging` | Auto-push + Deploy | 🟡 staging.tamam.shop |
| `prod` | Auto-push + Deploy | 🔴 prod.tamam.shop |
| `main` | Manual push | 💻 Development |
| Others | Manual push | 🔵 Feature branch |

### 📊 Branch-Specific Scopes
Scripts suggest relevant scopes based on current branch:

- **staging**: `staging, admin, api, ui`
- **prod**: `production, admin, hotfix, critical`
- **main**: `admin, api, ui, core, auth`
- **feature**: `feature, component, service, util`

---

## ⚙️ Configuration

### Team Members
Edit the team configuration in both scripts:

```bash
# In smart-commit.sh and quick-commit.sh
TEAM_KEYS=("hooshyar" "datacode" "backend")
TEAM_VALUES=(
    "Hooshyar <hooseyr@gmail.com>" 
    "Datacode-backend <ferbon.com@gmail.com>" 
    "backend <backend@tamam.krd>"
)
```

### Adding New Team Members
1. Add the key to `TEAM_KEYS` array
2. Add the full name and email to `TEAM_VALUES` array  
3. Ensure the arrays have matching indices

---

## 🎯 Workflow Integration

### Development Workflow
```bash
# 1. Development work
git checkout main
# Make changes...

# 2. Commit with smart script
./quick-commit.sh "feat(admin): new feature"

# 3. Push manually  
git push origin main
```

### Staging Deployment
```bash
# 1. Switch to staging
git checkout staging
git merge main  # or make direct changes

# 2. Commit (auto-deploys)
./quick-commit.sh "🚀 deploy(staging): release candidate v2.1"
# ↳ Automatically pushes and triggers staging deployment
```

### Production Deployment  
```bash
# 1. Switch to production
git checkout prod
git merge staging  # or cherry-pick specific commits

# 2. Commit (auto-deploys)
./quick-commit.sh "🚀 deploy(prod): release version 2.1.0"
# ↳ Automatically pushes and triggers production deployment
```

---

## 📋 Commit Types Reference

| Type | Emoji | Usage |
|------|-------|-------|
| `feat` | ✨ | New features |
| `fix` | 🔧 | Bug fixes |
| `docs` | 📚 | Documentation |
| `style` | 💎 | Code style/formatting |
| `refactor` | ♻️ | Code refactoring |
| `perf` | ⚡ | Performance improvements |
| `test` | 🧪 | Tests |
| `build` | 🏗️ | Build system |
| `ci` | 👷 | CI/CD |
| `chore` | 🧹 | Maintenance |
| `security` | 🔒 | Security fixes |
| `deploy` | 🚀 | Deployments |
| `hotfix` | 🚨 | Critical fixes |

---

## 🔧 Troubleshooting

### Script Permissions
```bash
chmod +x smart-commit.sh quick-commit.sh
```

### Git Configuration
```bash
# Ensure git user is configured
git config --global user.name "Your Name"
git config --global user.email "your.email@example.com"
```

### No Changes to Commit
Scripts automatically stage all changes with `git add -A`. If you see "No changes to commit", make sure you have modified files.

---

## 🎉 Benefits

### For Team Collaboration
- ✅ **Automatic co-author attribution**
- ✅ **Consistent commit message format**
- ✅ **No missed team member credits**
- ✅ **Clean git history**

### For Deployment Management  
- ✅ **Deployment branch awareness**
- ✅ **Automatic deployment triggering**
- ✅ **Clear deployment tracking**
- ✅ **Reduced manual errors**

### For Development Workflow
- ✅ **Faster commit process**
- ✅ **Standardized conventions** 
- ✅ **Built-in best practices**
- ✅ **Team member friendly**

---

## 📞 Support

For questions or improvements to these scripts:
- **Contact**: Hooshyar <hooseyr@gmail.com>
- **Repository**: datacode-app/tamam-admin-backend
- **Documentation**: This guide + script comments