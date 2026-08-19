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
            $table->unsignedTinyInteger('home_score_ht')->nullable()->after('away_score');
            $table->unsignedTinyInteger('away_score_ht')->nullable()->after('home_score_ht');
            $table->text('halftime_report')->nullable()->after('match_report');
            $table->timestamp('preview_published_at')->nullable()->after('away_preview_note');
            $table->timestamp('halftime_published_at')->nullable()->after('halftime_report');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('matches', function (Blueprint $table) {
            $table->dropColumn([
                'home_score_ht', 'away_score_ht', 'halftime_report',
                'preview_published_at', 'halftime_published_at',
            ]);
        });
    }
};
