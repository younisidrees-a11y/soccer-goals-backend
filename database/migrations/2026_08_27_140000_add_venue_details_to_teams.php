<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->string('venue_city')->nullable()->after('stadium_capacity');
            $table->string('venue_address')->nullable()->after('venue_city');
            $table->string('venue_surface')->nullable()->after('venue_address');
            $table->string('venue_image_url')->nullable()->after('venue_surface');
        });
    }

    public function down(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->dropColumn(['venue_city', 'venue_address', 'venue_surface', 'venue_image_url']);
        });
    }
};
