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
            $table->string('timezone')->default('UTC')->after('email');
            $table->string('date_format_preference')->default('Y-m-d')->after('timezone');
            $table->enum('time_format_preference', ['12h', '24h'])->default('12h')->after('date_format_preference');
            $table->enum('default_view', ['month', 'week', 'day', 'list'])->default('month')->after('time_format_preference');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['timezone', 'date_format_preference', 'time_format_preference', 'default_view']);
        });
    }
};
