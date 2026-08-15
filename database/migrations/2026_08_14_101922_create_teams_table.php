<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('league_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('full_name');
            $table->string('slug')->unique();
            $table->string('crest_code', 10)->unique();
            $table->string('color_hex', 7)->default('#1552C0');
            $table->string('stadium')->nullable();
            $table->string('stadium_capacity', 20)->nullable();
            $table->string('manager')->nullable();
            $table->unsignedSmallInteger('founded_year')->nullable();
            $table->text('history_essay')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teams');
    }
};
