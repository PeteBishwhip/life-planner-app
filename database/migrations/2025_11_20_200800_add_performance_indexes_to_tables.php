<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            // Index for date range queries
            $table->index(['start_datetime', 'end_datetime'], 'appointments_date_range_index');

            // Index for user appointments
            $table->index(['user_id', 'calendar_id'], 'appointments_user_calendar_index');

            // Index for status filtering
            $table->index(['status', 'start_datetime'], 'appointments_status_date_index');

            // Index for recurring appointments
            $table->index('recurrence_parent_id', 'appointments_recurrence_parent_index');

            // Index for user + start datetime (for search and filtering)
            $table->index(['user_id', 'start_datetime'], 'appointments_user_start_index');

            // Index for calendar + status (for filtering by calendar and status)
            $table->index(['calendar_id', 'status'], 'appointments_calendar_status_index');
        });

        Schema::table('calendars', function (Blueprint $table) {
            // Index for user visible calendars
            $table->index(['user_id', 'is_visible'], 'calendars_user_visible_index');

            // Index for default calendars
            $table->index(['user_id', 'is_default'], 'calendars_user_default_index');

            // Index for calendar type filtering
            $table->index('type', 'calendars_type_index');
        });

        Schema::table('appointment_reminders', function (Blueprint $table) {
            // Index for pending reminders
            $table->index(['is_sent', 'sent_at'], 'reminders_sent_index');

            // Index for appointment reminders lookup
            $table->index('appointment_id', 'reminders_appointment_index');

            // Index for finding unsent reminders
            $table->index('is_sent', 'reminders_is_sent_index');
        });

        Schema::table('import_logs', function (Blueprint $table) {
            // Index for user import history
            $table->index('user_id', 'import_logs_user_index');

            // Index for status filtering
            $table->index('status', 'import_logs_status_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropIndex('appointments_date_range_index');
            $table->dropIndex('appointments_user_calendar_index');
            $table->dropIndex('appointments_status_date_index');
            $table->dropIndex('appointments_recurrence_parent_index');
            $table->dropIndex('appointments_user_start_index');
            $table->dropIndex('appointments_calendar_status_index');
        });

        Schema::table('calendars', function (Blueprint $table) {
            $table->dropIndex('calendars_user_visible_index');
            $table->dropIndex('calendars_user_default_index');
            $table->dropIndex('calendars_type_index');
        });

        Schema::table('appointment_reminders', function (Blueprint $table) {
            $table->dropIndex('reminders_sent_index');
            $table->dropIndex('reminders_appointment_index');
            $table->dropIndex('reminders_is_sent_index');
        });

        Schema::table('import_logs', function (Blueprint $table) {
            $table->dropIndex('import_logs_user_index');
            $table->dropIndex('import_logs_status_index');
        });
    }
};
