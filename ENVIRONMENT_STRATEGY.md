# Environment Strategy Documentation

## Branch-Specific Database Architecture

This document outlines the comprehensive environment strategy for the TAMAM Admin Backend project, ensuring proper separation between development, staging, and production environments.

## Overview

The project follows a **3-branch strategy** with **separate DigitalOcean MySQL clusters** for staging and production:

- **Main Branch**: Local development using remote staging MySQL cluster
- **Staging Branch**: Staging server using remote staging MySQL cluster  
- **Production Branch**: Production server using dedicated production MySQL cluster

## Database Cluster Configuration

### Staging MySQL Cluster (Main & Staging Branches)
```
Host: tamam-staging-db-do-user-19403128-0.j.db.ondigitalocean.com
Port: 25060
Database: tamamdb_staging
Username: doadmin
Password: [Staging credentials in actual .env files]
```

### Production MySQL Cluster (Production Branch Only)
```
Host: tamam-production-db-do-user-19403128-0.j.db.ondigitalocean.com
Port: 25060
Database: tamamdb
Username: doadmin  
Password: [Production credentials in actual .env files]
```

## Environment Files Structure

### Template Files
- `.env.example` - Default template (uses staging cluster)
- `.env.main.example` - Main branch template (local development)
- `.env.staging.example` - Staging branch template (staging server)
- `.env.production.example` - Production branch template (production server)
- `.env.testing` - Testing environment (SQLite in-memory)

### Environment Creation Scripts
- `create-staging-env.sh` - Creates staging .env on staging server
- `create-production-env.sh` - Creates production .env on production server

## Server Infrastructure

### Staging Server (46.101.190.171)
- **Domain**: https://staging.tamam.shop
- **Branch**: staging
- **Database**: Staging MySQL Cluster
- **Storage**: tamam-staging DigitalOcean Space
- **Deployment**: `./deploy-to-staging.sh`

### Production Server (134.209.230.97)
- **Domain**: https://prod.tamam.shop  
- **Branch**: prod
- **Database**: Production MySQL Cluster
- **Storage**: tamam-prod DigitalOcean Space
- **Deployment**: `./deploy-to-production.sh`

## Deployment Workflow

### Staging Deployment
```bash
./deploy-to-staging.sh
```
- Deploys latest staging branch changes
- Creates staging .env with staging MySQL cluster
- Runs migrations on staging database
- Tests staging.tamam.shop functionality

### Production Deployment
```bash  
./deploy-to-production.sh
```
- Deploys latest prod branch changes
- Creates production .env with production MySQL cluster
- Runs migrations on production database
- Tests prod.tamam.shop functionality

## Testing Configuration

### Local Testing
- Uses `.env.testing` with SQLite in-memory database
- Configured in `phpunit.xml`
- Run with: `php artisan test`

### Testing Framework
- **PHPUnit**: Version 10.x
- **Collision**: Version 7.x (updated for compatibility)
- **Database**: SQLite in-memory for fast testing

## Storage Configuration

### DigitalOcean Spaces
- **Staging**: tamam-staging bucket (fra1 region)
- **Production**: tamam-prod bucket (fra1 region)
- **CDN**: Both use CDN endpoints for performance

### Local Development
- Uses `local` filesystem driver
- No cloud storage required for development

## Security Considerations

### Environment Separation
- Complete separation between staging and production databases
- Different storage buckets for each environment
- Separate SSH keys for server access
- Environment-specific application keys

### Credential Management
- Production credentials never stored in repository
- Placeholder values in template files
- Actual credentials managed securely on servers
- Regular credential rotation recommended

## Development Workflow

### Local Development (Main Branch)
1. Use `.env.main.example` as template
2. Connect to staging MySQL cluster for consistency
3. Test locally before pushing to staging
4. Use local filesystem for development speed

### Staging Testing (Staging Branch)
1. Deploy to staging server automatically
2. Use staging MySQL cluster and storage
3. Test all functionality before production
4. Monitor logs at staging.tamam.shop

### Production Deployment (Production Branch)
1. Deploy to production server with caution
2. Use production MySQL cluster exclusively
3. Monitor closely for 30 minutes post-deployment
4. Production-specific configurations applied

## Monitoring & Maintenance

### Log Monitoring
- Staging logs: `./check-staging-logs.sh` (if exists)
- Production logs: `./check-production-logs.sh`
- Real-time monitoring via SSH access

### Database Maintenance
- Regular backups managed by DigitalOcean
- Staging cluster: Shared between main/staging
- Production cluster: Isolated and protected
- Monitor performance and connection limits

### File Cleanup
- Remove test/verification files after development
- Keep repository clean and production-ready
- Use .gitignore for environment-specific files

## Emergency Procedures

### Production Issues
1. Check production logs immediately
2. Consider rollback to previous stable version
3. Use staging environment to test fixes
4. Monitor production metrics continuously

### Database Issues
- Staging problems affect both main and staging branches
- Production database is completely isolated
- DigitalOcean support available for cluster issues
- Regular backups available for recovery

## Migration Strategy

When migrating between environments:
1. Test migrations thoroughly in staging
2. Verify data integrity after migration
3. Check all application functionality
4. Monitor performance impact

This architecture ensures complete separation between staging and production while maintaining consistency in the development workflow.