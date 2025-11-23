# Life Planner

A modern, mobile-friendly calendar and appointment management application built with Laravel, Livewire, and Tailwind CSS.

![Build Status](https://img.shields.io/badge/build-passing-brightgreen)
![Laravel](https://img.shields.io/badge/Laravel-12-red)
![License](https://img.shields.io/badge/license-MIT-blue)

## Features

### Core Functionality
- 📅 **Multiple Calendar Support** - Personal, Business, and Custom calendars
- 🔄 **Recurring Appointments** - Daily, weekly, monthly, and yearly patterns
- ⚡ **Smart Conflict Detection** - Cross-calendar appointment blocking
- 📱 **Mobile-First Design** - Fully responsive with PWA capabilities
- 🎯 **Quick Add** - Natural language appointment creation
- 🔍 **Advanced Search & Filtering** - Find appointments quickly
- 📥📤 **Import/Export** - ICS, CSV, and PDF formats
- 🔔 **Notifications** - Email reminders and browser notifications
- ⌨️ **Keyboard Shortcuts** - Navigate faster with keyboard commands

### Views
- Month View - Traditional calendar grid
- Week View - 7-day schedule
- Day View - Detailed daily schedule
- List View - Agenda-style view

## Technology Stack

- **Framework**: Laravel 12
- **Frontend**: Livewire 3, Tailwind CSS 3, Alpine.js
- **Database**: PostgreSQL (MySQL/SQLite supported)
- **Cache/Queue**: Redis
- **Authentication**: Laravel Breeze
- **Testing**: PHPUnit (374 tests, 1113 assertions)

## Requirements

- PHP 8.3+
- Composer
- Node.js 20+
- PostgreSQL 16+ (or MySQL 8+)
- Redis 7+

## Installation

### Quick Start with Docker

```bash
# Clone the repository
git clone https://github.com/yourusername/life-planner.git
cd life-planner

# Copy environment file
cp .env.example .env

# Update .env with your settings

# Start with Docker Compose
docker-compose up -d

# Generate application key
docker-compose exec app php artisan key:generate

# Run migrations
docker-compose exec app php artisan migrate

# Visit http://localhost:8000
```

### Manual Installation

```bash
# Clone the repository
git clone https://github.com/yourusername/life-planner.git
cd life-planner

# Install PHP dependencies
composer install

# Install Node dependencies
npm install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Configure your database in .env

# Run migrations
php artisan migrate

# Build frontend assets
npm run build

# Start the development server
php artisan serve
```

Visit `http://localhost:8000` to access the application.

## Configuration

### Database Setup

**PostgreSQL (Recommended)**
```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=life_planner
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

**MySQL**
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=life_planner
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### Email Configuration

For production, configure a mail service:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.sendgrid.net
MAIL_PORT=587
MAIL_USERNAME=apikey
MAIL_PASSWORD=your-sendgrid-api-key
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
```

### Cache & Queue

```env
CACHE_STORE=redis
QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
```

## Usage

### Creating Appointments

**Quick Add (Natural Language)**
```
"Team meeting tomorrow at 2pm"
"Doctor appointment Friday at 10am for 30 minutes"
"Lunch at The Restaurant next Monday at noon"
```

**Manual Creation**
1. Click the "+" button or press `C` key
2. Fill in appointment details
3. Select calendar, date, time
4. Add reminders if needed
5. Click "Create"

### Managing Calendars

1. Go to Settings → Calendars
2. Create new calendars with custom colors
3. Toggle visibility to show/hide in views
4. Set default calendar for new appointments

### Import/Export

**Import**
- Supports ICS, Google Calendar, Outlook Calendar
- Go to Import/Export → Import
- Select file and target calendar
- Review preview before confirming

**Export**
- Export to ICS for calendar apps
- Export to PDF for printing
- Export to CSV for spreadsheets

### Keyboard Shortcuts

- `t` - Go to Today
- `n` - Next period
- `p` - Previous period
- `c` - Create appointment
- `q` - Quick add
- `s` - Search
- `?` - Show help

## Development

### Running Tests

```bash
# Run all tests
./vendor/bin/phpunit

# Run specific test suite
./vendor/bin/phpunit tests/Feature/AppointmentFeatureTest.php

# Run with coverage
./vendor/bin/phpunit --coverage-html coverage
```

Current test coverage: **374 tests, 1113 assertions** ✅

### Code Quality

```bash
# Format code with Laravel Pint
./vendor/bin/pint

# Run static analysis
./vendor/bin/phpstan analyse
```

### Building Assets

```bash
# Development
npm run dev

# Production build
npm run build

# Watch for changes
npm run dev -- --watch
```

## Deployment

### Using Docker

```bash
# Build and deploy
docker-compose up -d --build

# Run migrations
docker-compose exec app php artisan migrate --force

# Optimize
docker-compose exec app php artisan optimize
```

### Manual Deployment

```bash
# Use the deployment script
./deploy.sh
```

Or manually:

```bash
# Pull changes
git pull origin main

# Install dependencies
composer install --no-dev --optimize-autoloader
npm ci && npm run build

# Run migrations
php artisan migrate --force

# Optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Restart queue workers
php artisan queue:restart
```

## Performance

- Uses database indexing for fast queries (<500ms for 1000+ appointments)
- Redis caching for improved performance
- Lazy loading for large datasets
- Optimized asset bundling with Vite
- Dashboard loads in <2 seconds

## Security

- CSRF protection enabled
- XSS prevention via Blade escaping
- SQL injection prevention via Eloquent ORM
- Password hashing with bcrypt
- Rate limiting on authentication routes
- Input validation and sanitization
- All 25+ security tests passing ✅

## Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## Testing

We maintain high test coverage with:
- ✅ Unit tests for models and services
- ✅ Feature tests for user workflows
- ✅ Security tests for vulnerabilities
- ✅ Performance benchmarks
- ✅ Integration tests for complete workflows
- ✅ Livewire component tests
- ✅ 374 total tests with 1113 assertions

## Documentation

- [User Manual](docs/USER_MANUAL.md) - Complete guide for end users
- [API Documentation](docs/API.md) - API endpoint reference
- [Deployment Guide](docs/DEPLOYMENT.md) - Production deployment instructions

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Support

For support, email support@lifeplanner.app or open an issue on GitHub.

## Roadmap

- [ ] Mobile native apps (iOS/Android)
- [ ] Calendar sharing and collaboration
- [ ] Video conferencing integration
- [ ] AI-powered scheduling suggestions
- [ ] Meeting scheduling links
- [ ] Advanced reporting and analytics

## Acknowledgments

- Built with [Laravel](https://laravel.com)
- UI powered by [Tailwind CSS](https://tailwindcss.com)
- Reactive components with [Livewire](https://livewire.laravel.com)
- Icons from [Heroicons](https://heroicons.com)

---

**Made with ❤️ for better time management**
