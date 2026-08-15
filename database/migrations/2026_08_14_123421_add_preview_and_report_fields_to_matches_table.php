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
        Schema::table('matches', function (Blueprint $table) {
            $table->text('home_preview_note')->nullable()->after('venue');
            $table->text('away_preview_note')->nullable()->after('home_preview_note');
            $table->text('match_report')->nullable()->after('away_score');
            $table->json('stats')->nullable()->after('match_report');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('matches', function (Blueprint $table) {
            $table->dropColumn(['home_preview_note', 'away_preview_note', 'match_report', 'stats']);
        });
    }
};
