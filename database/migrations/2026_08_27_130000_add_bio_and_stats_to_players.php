<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('players', function (Blueprint $table) {
            $table->date('birth_date')->nullable()->after('nationality');
            $table->string('birth_place')->nullable()->after('birth_date');
            $table->string('birth_country')->nullable()->after('birth_place');
            $table->string('height')->nullable()->after('birth_country');
            $table->string('weight')->nullable()->after('height');
            $table->boolean('injured')->default(false)->after('weight');
            $table->json('stats')->nullable()->after('assists');
            $table->json('trophies')->nullable()->after('stats');
            $table->json('transfers')->nullable()->after('trophies');
            $table->string('meta_description')->nullable()->after('transfers');
        });
    }

    public function down(): void
    {
        Schema::table('players', function (Blueprint $table) {
            $table->dropColumn([
                'birth_date', 'birth_place', 'birth_country', 'height', 'weight', 'injured',
                'stats', 'trophies', 'transfers', 'meta_description',
            ]);
        });
    }
};
