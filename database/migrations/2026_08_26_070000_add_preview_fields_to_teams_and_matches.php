<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->unsignedInteger('coach_age')->nullable()->after('manager');
            $table->string('coach_nationality')->nullable()->after('coach_age');
            $table->timestamp('coach_synced_at')->nullable()->after('coach_nationality');
        });

        Schema::table('matches', function (Blueprint $table) {
            $table->json('prediction')->nullable()->after('motm');
        });
    }

    public function down(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->dropColumn(['coach_age', 'coach_nationality', 'coach_synced_at']);
        });

        Schema::table('matches', function (Blueprint $table) {
            $table->dropColumn('prediction');
        });
    }
};
