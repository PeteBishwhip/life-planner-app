# WARP.md

This file provides guidance to WARP (warp.dev) when working with code in this repository.

## Commands

### Development
- **Start dev server with all services**: `composer dev`
  - Runs Laravel server, queue worker, logs viewer (Pail), and Vite concurrently
- **Start dev server only**: `php artisan serve`
- **Start frontend dev**: `npm run dev`
- **Build frontend assets**: `npm run build`

### Testing
- **Run all tests**: `./vendor/bin/pest`
- **Run specific test file**: `./vendor/bin/pest tests/Feature/AppointmentTest.php`
- **Run tests in specific directory**: `./vendor/bin/pest tests/Unit`
- **Run test with filter**: `./vendor/bin/pest --filter=test_name`

### Code Quality
- **Format code (Laravel Pint)**: `./vendor/bin/pint`
- **Format specific files**: `./vendor/bin/pint app/Models/Appointment.php`

### Database
- **Run migrations**: `php artisan migrate`
- **Fresh migrate with seed**: `php artisan migrate:fresh --seed`
- **Rollback migration**: `php artisan migrate:rollback`

### Setup
- **Initial setup**: `composer setup`
  - Installs dependencies, creates .env, generates key, runs migrations, builds assets
- **Install git hooks**: `composer install-hooks`
  - Sets up pre-commit hook that runs Pint automatically

### Logs
- **View logs in real-time**: `php artisan pail`
- **View logs with timeout**: `php artisan pail --timeout=0`

## Architecture

### Technology Stack
- **Backend**: Laravel 12 (PHP 8.2+)
- **Frontend**: Livewire 3 + Alpine.js + Tailwind CSS
- **Calendar Engine**: Zap for Laravel
- **Auth**: Laravel Breeze (Livewire + Tailwind)
- **Permissions**: Spatie Laravel Permission
- **PDF Generation**: Barryvdh Laravel DomPDF
- **iCalendar**: Kigkonsult iCalCreator + Spatie iCalendar Generator

### Application Structure

#### Models (`app/Models/`)
- **Appointment**: Calendar appointments with support for recurring events, all-day events, and cross-calendar conflict detection
- **Calendar**: Multiple calendars per user (personal/business/custom) with color coding and visibility controls
- **AppointmentReminder**: Email and browser notification reminders for appointments
- **ImportLog**: Tracks calendar import operations (ICS, CSV, Google, Outlook)

#### Services (`app/Services/`)
Core business logic is encapsulated in service classes:
- **ConflictDetectionService**: Cross-calendar conflict detection and smart appointment blocking (business appointments block personal calendar and vice versa)
- **RecurrenceService**: Generates recurring appointment instances with support for daily, weekly, monthly, yearly patterns
- **IcsImportService**: Imports iCalendar (.ics) files
- **IcsExportService**: Exports calendars to iCalendar format
- **PdfExportService**: Generates PDF exports of calendars
- **CsvExportService**: Exports calendar data to CSV
- **ReminderService**: Handles appointment reminders and notifications

#### Livewire Components (`app/Livewire/`)
Full-page components:
- **CalendarDashboard**: Main calendar view with month/week/day view switching
- **AppointmentManager**: Create/edit/delete appointments with conflict warnings
- **CalendarSettings**: Manage multiple calendars, colors, and visibility
- **ImportExportManager**: Calendar import/export functionality

View components:
- **MonthView**, **WeekView**, **DayView**: Different calendar view renderings

### Key Features

#### Smart Appointment Blocking
The app automatically detects conflicts across different calendars. When scheduling:
- Business appointments block corresponding time on personal calendar
- Personal appointments block corresponding time on business calendar
- Visual indicators show blocked time slots
- Override option available for intentional overlaps

Use `ConflictDetectionService` methods:
- `hasConflictAcrossCalendars()`: Check for conflicts
- `findConflicts()`: Get conflicting appointments
- `getBlockedSlots()`: Get blocked time slots for a calendar
- `canSchedule()`: Validate if appointment can be scheduled

#### Recurring Appointments
Supports complex recurrence patterns via `RecurrenceService`:
- Daily, weekly, monthly, yearly frequencies
- Custom intervals (e.g., every 2 weeks)
- Specific days of week for weekly recurrence
- Specific day of month for monthly recurrence
- End date or occurrence count limits
- `generateInstances()` creates individual instances within a date range

#### Multi-Calendar Support
Users can manage multiple calendars:
- Pre-configured types: personal, business, custom
- Individual color coding
- Toggle visibility per calendar
- Unified or individual calendar views

### Database Schema
Key tables:
- `users`: User accounts with timezone and view preferences
- `calendars`: User calendars with type, color, visibility settings
- `appointments`: Calendar events with recurrence rules, status tracking
- `appointment_reminders`: Notification settings per appointment
- `import_logs`: Import operation tracking and error logging

### Mobile-Friendly Design
- Responsive breakpoints: < 640px (mobile), 640px-1024px (tablet), > 1024px (desktop)
- Touch-optimized interactions
- PWA capabilities configured
- Mobile-first appointment creation flow

## Development Notes

### Authentication
Uses Laravel Breeze with Livewire stack. Auth components in `app/Livewire/Actions/` and `app/Livewire/Forms/`.

### Testing Strategy
- Tests use SQLite in-memory database (configured in phpunit.xml)
- Test suites: Unit (tests/Unit) and Feature (tests/Feature)
- Follow existing test patterns for services and models

### Code Style
- Laravel Pint configured for code formatting
- Pre-commit git hook auto-runs Pint before commits
- Install hooks with `composer install-hooks`

### Date/Time Handling
- All datetime operations use Carbon
- Appointments stored in UTC, converted per user timezone
- `start_datetime` and `end_datetime` are datetime cast in Appointment model

### Livewire Patterns
- Use Livewire Volt for simple components where appropriate
- Full-page components for major features (Dashboard, Manager, Settings)
- Nested components for views (Month, Week, Day)
- Follow existing naming conventions in `app/Livewire/`

### Service Layer Pattern
Business logic belongs in service classes, not controllers or Livewire components. Services handle:
- Complex calculations (conflicts, recurrence)
- External integrations (import/export)
- Cross-cutting concerns (reminders, notifications)

### Scopes and Query Optimization
Appointment model includes useful query scopes:
- `forUser($userId)`: Filter by user
- `forCalendar($calendarId)`: Filter by calendar
- `betweenDates($start, $end)`: Date range queries
- `upcoming()`: Future scheduled appointments
- `scheduled()`, `completed()`, `cancelled()`: Status filters
- `allDay()`: All-day events only

Use these scopes to maintain consistency and readability across queries.

### Import/Export
- ICS import/export for standard calendar compatibility
- Google Calendar and Outlook integration via OAuth (Socialite)
- PDF generation for printable calendars
- CSV export for data portability
- Import operations logged in `import_logs` table

## Project Status
Currently in Phase 5 (Import/Export completed). Next phase focuses on search/filtering, quick add, keyboard shortcuts, and notification system. See PLAN.md for detailed roadmap.
