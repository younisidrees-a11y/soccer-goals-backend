<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * api_football_fixture_id had no database-level uniqueness guarantee
 * since it was added - the only thing preventing duplicate rows for the
 * same real fixture was updateOrCreate()'s own find-then-save logic,
 * which isn't atomic. Confirmed live: a race between an automatically-
 * scheduled sync and a manually-run one let two processes both fail to
 * find an existing row and both insert - 16 distinct fixtures, 1,076
 * duplicate rows total (one match had 75 copies of itself). Run
 * matches:dedupe-api-fixtures before this migration, or it will fail to
 * apply while duplicates still exist.
 *
 * Nullable + unique is intentional and safe in MySQL: NULL is never
 * considered equal to another NULL under a unique index, so the many
 * real rows sourced from football-data.org (which never sets this
 * column at all) can all stay NULL without conflicting with each other.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('matches', function (Blueprint $table) {
            $table->unique('api_football_fixture_id');
        });
    }

    public function down(): void
    {
        Schema::table('matches', function (Blueprint $table) {
            $table->dropUnique(['api_football_fixture_id']);
        });
    }
};
