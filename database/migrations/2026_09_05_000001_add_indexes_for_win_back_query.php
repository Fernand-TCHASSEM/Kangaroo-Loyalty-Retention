<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Support the coarse win-back filter that runs in SQL: an inactivity
     * scan on customers.last_activity_at, and the correlated lookup of the
     * next reward threshold on rewards.points_required.
     */
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->index('last_activity_at');
        });

        Schema::table('rewards', function (Blueprint $table) {
            $table->index('points_required');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropIndex(['last_activity_at']);
        });

        Schema::table('rewards', function (Blueprint $table) {
            $table->dropIndex(['points_required']);
        });
    }
};
