<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $tables = ['leagues', 'teams', 'matches', 'news_articles'];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->string('meta_title')->nullable();
                $blueprint->string('meta_description', 500)->nullable();
                $blueprint->string('meta_keywords')->nullable();
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->dropColumn(['meta_title', 'meta_description', 'meta_keywords']);
            });
        }
    }
};
