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
        Schema::table('leagues', function (Blueprint $table) {
            $table->unsignedInteger('api_football_id')->nullable()->after('external_code');
        });

        Schema::table('teams', function (Blueprint $table) {
            $table->unsignedInteger('api_football_id')->nullable()->after('external_id');
        });

        Schema::table('matches', function (Blueprint $table) {
            $table->unsignedInteger('api_football_fixture_id')->nullable()->after('external_id');
            $table->json('events')->nullable()->after('stats');
            $table->json('lineups')->nullable()->after('events');
            $table->json('motm')->nullable()->after('lineups');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leagues', function (Blueprint $table) {
            $table->dropColumn('api_football_id');
        });

        Schema::table('teams', function (Blueprint $table) {
            $table->dropColumn('api_football_id');
        });

        Schema::table('matches', function (Blueprint $table) {
            $table->dropColumn(['api_football_fixture_id', 'events', 'lineups', 'motm']);
        });
    }
};
