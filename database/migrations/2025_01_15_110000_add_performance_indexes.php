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
            // Composite index for user + date queries
            $table->index(['user_id', 'start_datetime'], 'idx_user_id_start_datetime');

            // Composite index for calendar + status queries
            $table->index(['calendar_id', 'status'], 'idx_calendar_id_status');

            // Composite index for status + date range queries
            $table->index(['status', 'start_datetime'], 'idx_status_start_datetime');

            // Index for recurring appointment lookups
            $table->index('recurrence_parent_id', 'idx_recurrence_parent_id');

            // Index for end_datetime for overlap detection
            $table->index('end_datetime', 'idx_end_datetime');
        });

        Schema::table('calendars', function (Blueprint $table) {
            // Composite index for user + visibility queries
            $table->index(['user_id', 'is_visible'], 'idx_user_id_is_visible');

            // Composite index for finding default calendar
            $table->index(['user_id', 'is_default'], 'idx_user_id_is_default');

            // Index for calendar type filtering
            $table->index('type', 'idx_type');
        });

        Schema::table('appointment_reminders', function (Blueprint $table) {
            // Composite index for unsent reminders per appointment
            $table->index(['appointment_id', 'is_sent'], 'idx_appointment_id_is_sent');

            // Index for finding all unsent reminders
            $table->index('is_sent', 'idx_is_sent');

            // Index for reminder time calculations
            $table->index('reminder_minutes_before', 'idx_reminder_minutes_before');
        });

        Schema::table('import_logs', function (Blueprint $table) {
            // Index for user import history
            $table->index('user_id', 'idx_user_id');

            // Index for status filtering
            $table->index('status', 'idx_status');

            // Composite index for user + status queries
            $table->index(['user_id', 'status'], 'idx_user_id_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropIndex('idx_user_id_start_datetime');
            $table->dropIndex('idx_calendar_id_status');
            $table->dropIndex('idx_status_start_datetime');
            $table->dropIndex('idx_recurrence_parent_id');
            $table->dropIndex('idx_end_datetime');
        });

        Schema::table('calendars', function (Blueprint $table) {
            $table->dropIndex('idx_user_id_is_visible');
            $table->dropIndex('idx_user_id_is_default');
            $table->dropIndex('idx_type');
        });

        Schema::table('appointment_reminders', function (Blueprint $table) {
            $table->dropIndex('idx_appointment_id_is_sent');
            $table->dropIndex('idx_is_sent');
            $table->dropIndex('idx_reminder_minutes_before');
        });

        Schema::table('import_logs', function (Blueprint $table) {
            $table->dropIndex('idx_user_id');
            $table->dropIndex('idx_status');
            $table->dropIndex('idx_user_id_status');
        });
    }
};
