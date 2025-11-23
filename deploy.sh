#!/bin/bash

# Life Planner Deployment Script
# This script handles the deployment process for the Life Planner application

set -e

echo "🚀 Starting Life Planner Deployment..."

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Check if .env file exists
if [ ! -f .env ]; then
    echo -e "${YELLOW}⚠️  .env file not found. Copying from .env.example...${NC}"
    cp .env.example .env
    echo -e "${YELLOW}⚠️  Please update .env with your configuration before proceeding!${NC}"
    exit 1
fi

# Pull latest changes
echo -e "${GREEN}📥 Pulling latest changes...${NC}"
git pull origin main

# Install Composer dependencies
echo -e "${GREEN}📦 Installing Composer dependencies...${NC}"
composer install --no-dev --optimize-autoloader --no-interaction

# Install NPM dependencies
echo -e "${GREEN}📦 Installing NPM dependencies...${NC}"
npm ci

# Build assets
echo -e "${GREEN}🏗️  Building frontend assets...${NC}"
npm run build

# Clear and cache config
echo -e "${GREEN}⚙️  Optimizing configuration...${NC}"
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations
echo -e "${GREEN}🗄️  Running database migrations...${NC}"
php artisan migrate --force

# Clear application cache
echo -e "${GREEN}🧹 Clearing application cache...${NC}"
php artisan cache:clear

# Restart queue workers
echo -e "${GREEN}🔄 Restarting queue workers...${NC}"
php artisan queue:restart

# Set proper permissions
echo -e "${GREEN}🔐 Setting permissions...${NC}"
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

echo -e "${GREEN}✅ Deployment completed successfully!${NC}"
echo -e "${GREEN}🎉 Life Planner is now running the latest version!${NC}"
