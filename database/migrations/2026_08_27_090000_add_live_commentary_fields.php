<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leagues', function (Blueprint $table) {
            $table->boolean('live_commentary_enabled')->default(false)->after('api_football_id');
        });

        Schema::table('matches', function (Blueprint $table) {
            $table->json('commentary')->nullable()->after('prediction');
        });
    }

    public function down(): void
    {
        Schema::table('leagues', function (Blueprint $table) {
            $table->dropColumn('live_commentary_enabled');
        });

        Schema::table('matches', function (Blueprint $table) {
            $table->dropColumn('commentary');
        });
    }
};
