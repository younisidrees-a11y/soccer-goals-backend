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
        Schema::table('teams', function (Blueprint $table) {
            $table->text('manager_facts')->nullable()->after('manager');
            $table->text('manager_bio')->nullable()->after('manager_facts');
            $table->string('manager_photo_path')->nullable()->after('manager_bio');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->dropColumn(['manager_facts', 'manager_bio', 'manager_photo_path']);
        });
    }
};
