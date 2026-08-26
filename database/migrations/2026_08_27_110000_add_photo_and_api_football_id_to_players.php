<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('players', function (Blueprint $table) {
            $table->unsignedInteger('api_football_id')->nullable()->after('team_id');
            $table->string('photo_url')->nullable()->after('nationality');
        });

        Schema::table('players', function (Blueprint $table) {
            $table->unique(['team_id', 'api_football_id']);
        });
    }

    public function down(): void
    {
        Schema::table('players', function (Blueprint $table) {
            $table->dropUnique(['team_id', 'api_football_id']);
            $table->dropColumn(['api_football_id', 'photo_url']);
        });
    }
};
