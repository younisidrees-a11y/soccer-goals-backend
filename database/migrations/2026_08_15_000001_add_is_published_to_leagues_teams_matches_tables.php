<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leagues', function (Blueprint $table) {
            $table->boolean('is_published')->default(false)->after('meta_keywords');
        });

        Schema::table('teams', function (Blueprint $table) {
            $table->boolean('is_published')->default(false)->after('meta_keywords');
        });

        Schema::table('matches', function (Blueprint $table) {
            $table->boolean('is_published')->default(false)->after('meta_keywords');
        });
    }

    public function down(): void
    {
        Schema::table('leagues', function (Blueprint $table) {
            $table->dropColumn('is_published');
        });

        Schema::table('teams', function (Blueprint $table) {
            $table->dropColumn('is_published');
        });

        Schema::table('matches', function (Blueprint $table) {
            $table->dropColumn('is_published');
        });
    }
};
