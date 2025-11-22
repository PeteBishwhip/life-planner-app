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
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('enable_email_notifications')->default(true)->after('default_view');
            $table->boolean('enable_browser_notifications')->default(true)->after('enable_email_notifications');
            $table->boolean('enable_daily_digest')->default(true)->after('enable_browser_notifications');
            $table->time('daily_digest_time')->default('06:00:00')->after('enable_daily_digest');
            $table->string('week_start_day')->default('monday')->after('daily_digest_time');
            $table->integer('default_appointment_duration')->default(60)->after('week_start_day');
            $table->json('default_reminder_times')->nullable()->after('default_appointment_duration');
            $table->string('theme')->default('light')->after('default_reminder_times');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'enable_email_notifications',
                'enable_browser_notifications',
                'enable_daily_digest',
                'daily_digest_time',
                'week_start_day',
                'default_appointment_duration',
                'default_reminder_times',
                'theme',
            ]);
        });
    }
};
