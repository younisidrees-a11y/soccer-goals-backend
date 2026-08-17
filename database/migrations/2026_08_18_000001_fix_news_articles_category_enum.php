<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * The category ENUM was created as ('match-report','transfers','analysis',
 * 'opinion','injuries'), but every other part of the app - the nav menu,
 * NewsController::CATEGORIES, and the Filament form - has always used
 * ('match-report','transfers','analysis','injury','club-news'). Nothing
 * had ever tried to insert 'injury' or 'club-news' until now, which is
 * why this mismatch went unnoticed. This aligns the column with what the
 * app actually uses everywhere else.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE news_articles MODIFY category ENUM('match-report', 'transfers', 'analysis', 'injury', 'club-news') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE news_articles MODIFY category ENUM('match-report', 'transfers', 'analysis', 'opinion', 'injuries') NOT NULL");
    }
};
