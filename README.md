# TAMAM Admin Backend - Laravel API & Dashboard

![Laravel](https://img.shields.io/badge/Laravel-10.x-FF2D20?style=flat&logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.1+-777BB4?style=flat&logo=php&logoColor=white)
![Multi-Module](https://img.shields.io/badge/Architecture-Multi--Module-blue)
![Staging Deployment](https://github.com/YOUR_USERNAME/YOUR_REPO_NAME/actions/workflows/deploy-staging.yml/badge.svg)

## 🚀 Deployment Status

Last Updated: September 13, 2025 - Comprehensive deployment workflows active

## Overview

The TAMAM Admin Backend is a comprehensive Laravel 10.x application that powers the entire TAMAM delivery and rental ecosystem. It provides a robust REST API for mobile applications and a full-featured admin dashboard for system management.

## 🏗️ Architecture

- **Framework**: Laravel 10.x with multi-module architecture
- **Database**: MySQL with comprehensive migrations and seeders
- **API**: RESTful endpoints for mobile apps (Business, Driver, Customer)
- **Admin Panel**: Full-featured web dashboard
- **Provider Portal**: Service partner management interface

## 📦 Key Modules

### Core Delivery System
- Multi-vendor restaurant management
- Order processing and tracking
- Real-time delivery coordination
- Payment gateway integrations

### Rental System Module (`Modules/Rental/`)
- Vehicle category and brand management
- Driver and provider management
- Trip booking and management
- Rental cart and wishlist functionality
- Comprehensive reporting system

## 🚀 Features

### Admin Dashboard
- **Restaurant Management**: Add, edit, and manage restaurants
- **Menu Management**: Category and item management with multilingual support
- **Order Tracking**: Real-time order status monitoring
- **User Management**: Customer, driver, and provider administration
- **Financial Reports**: Comprehensive revenue and transaction reports
- **Settings Management**: System configuration and business rules

### Provider Portal
- **Business Dashboard**: Performance metrics and analytics
- **Vehicle Management**: Fleet management for rental providers
- **Trip Management**: Booking and scheduling system
- **Driver Assignment**: Driver allocation and tracking
- **Financial Reports**: Earnings and commission tracking

### REST API
- **Authentication**: JWT-based user authentication
- **Multi-language**: Arabic, English, and Kurdish support
- **Real-time Updates**: WebSocket integration for live updates
- **File Management**: Image upload and processing
- **Push Notifications**: Firebase Cloud Messaging integration

## 🛠️ Technical Stack

- **Backend**: Laravel 10.x, PHP 8.1+
- **Database**: MySQL with Redis caching
- **Queue System**: Redis-based job processing
- **File Storage**: Local and cloud storage support
- **Email**: SMTP and mail queue system
- **Localization**: Multi-language support (ar, en, ckb)

## 📱 Mobile App Integration

This backend serves three Flutter mobile applications:
- **TAMAM Business**: Restaurant and business partner app
- **TAMAM Driver**: Delivery driver application
- **TAMAM Shop**: Customer shopping application

## 🔧 Installation & Setup

```bash
# Install dependencies
composer install
npm install

# Environment setup
cp .env.example .env
php artisan key:generate

# Database setup
php artisan migrate
php artisan db:seed

# Build assets
npm run build

# Start development server
php artisan serve
```

## 🗂️ Project Structure

```
Admin-with-Rental/
├── app/                    # Core application code
├── config/                 # Configuration files
├── database/              # Migrations, seeders, factories
├── Modules/               # Modular architecture
│   └── Modules/Rental/    # Rental system module
├── public/                # Web server document root
├── resources/             # Views, assets, lang files
├── routes/                # Route definitions
└── scripts/git/           # Multi-user git workflow
```

## 🚀 Automated Deployment

### Staging Server
- **URL**: https://staging.tamam.shop/admin
- **Auto-Deploy**: Triggers on every push to `main` branch
- **Workflow**: `.github/workflows/deploy-staging.yml`

### Deployment Process
1. **Automated**: Push to main → GitHub Actions → Deploy to staging
2. **Manual**: Run `./deploy-to-staging.sh` for immediate deployment
3. **Testing**: Automated route testing after deployment

### Setup Instructions
See [.github/DEPLOYMENT_SETUP.md](.github/DEPLOYMENT_SETUP.md) for GitHub secrets configuration.

## 🔐 Security Features

- **Input Validation**: Comprehensive request validation
- **CSRF Protection**: Cross-site request forgery protection
- **Rate Limiting**: API rate limiting and throttling
- **SQL Injection Prevention**: Eloquent ORM protection
- **XSS Protection**: Output sanitization
- **Authentication**: Multi-layer authentication system

## 📊 Reporting & Analytics

- **Sales Reports**: Revenue tracking and analysis
- **User Analytics**: Customer behavior insights
- **Performance Metrics**: System performance monitoring
- **Financial Reports**: Commission and payment tracking

## 🌍 Internationalization

Full support for:
- **Arabic** (ar): Complete RTL support
- **English** (en): Default language
- **Kurdish Sorani** (ckb): Native Kurdish support

## 🤝 Development Team

- **Backend Team**: `Datacode-backend` (backend@datacode.dev)
- **Mobile Team**: `datacode-mobile` (frontend@datacode.dev)
- **DevOps Team**: `datacode-devops` (devops@datacode.dev)
- **Contact**: info@datacode.app

## 🔄 Git Workflow

This repository uses an intelligent multi-user git workflow:

```bash
# Use smart commit system
./scripts/git/smart-commit.sh

# Automatically handles:
# - User attribution based on file changes
# - AI-generated commit messages
# - Conventional commit format
```

## 📋 API Documentation

The API serves multiple client applications with comprehensive endpoints for:
- User authentication and management
- Restaurant and menu operations
- Order processing and tracking
- Payment and transaction handling
- Real-time notifications and updates

## 🎯 Production Deployment

- **Environment**: Production-ready Laravel configuration
- **Caching**: Redis-based caching strategy
- **Queue Processing**: Background job handling
- **Monitoring**: Error tracking and performance monitoring
- **Backup**: Automated database and file backups

## 📞 Support

For technical support and development questions:
- Backend issues: Contact Datacode-backend team
- General Contact: info@datacode.app
- API integration: Refer to API documentation
- Multi-user workflow: Use provided git scripts

---

**TAMAM Admin Backend** - Powering the complete delivery and rental ecosystem for Kurdistan and Iraq.

**Developed by Datacode**