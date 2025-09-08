#!/bin/bash

echo "🚀 Setting up Tamam Multi-Vendor Platform with Docker..."

# Copy Docker environment file
if [ ! -f .env ]; then
    echo "📄 Copying Docker environment configuration..."
    cp .env.docker .env
fi

# Build and start containers
echo "🔨 Building Docker containers..."
docker-compose build --no-cache

echo "📦 Starting Docker containers..."
docker-compose up -d

# Wait for MySQL to be ready
echo "⏳ Waiting for MySQL to be ready..."
sleep 30

# Run Laravel setup commands
echo "🔧 Setting up Laravel application..."

# Generate app key if needed
docker-compose exec app php artisan key:generate --force

# Clear caches
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan view:clear
docker-compose exec app php artisan route:clear

# Create storage link
docker-compose exec app php artisan storage:link

# Run migrations
echo "📊 Running database migrations..."
docker-compose exec app php artisan migrate --force

# Seed database
echo "🌱 Seeding database..."
docker-compose exec app php artisan db:seed --force

# Set permissions
echo "🔐 Setting permissions..."
docker-compose exec app chown -R www-data:www-data /var/www/html/storage
docker-compose exec app chown -R www-data:www-data /var/www/html/bootstrap/cache

echo "✅ Setup complete!"
echo ""
echo "🌐 Access points:"
echo "   📱 Application: http://localhost:8000"
echo "   🗄️  phpMyAdmin: http://localhost:8080"
echo "   📊 Admin Panel: http://localhost:8000/admin"
echo ""
echo "🔑 Default admin login:"
echo "   📧 Email: admin@admin.com"
echo "   🔒 Password: 12345678"
echo ""
echo "🛠️  Useful commands:"
echo "   📋 View logs: docker-compose logs -f app"
echo "   🔧 Laravel commands: docker-compose exec app php artisan [command]"
echo "   🛑 Stop containers: docker-compose down"
echo "   🔄 Restart: docker-compose restart"