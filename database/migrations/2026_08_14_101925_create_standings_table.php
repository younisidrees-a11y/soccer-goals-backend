<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('standings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('league_id')->constrained()->cascadeOnDelete();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('position');
            $table->unsignedTinyInteger('played')->default(0);
            $table->unsignedTinyInteger('won')->default(0);
            $table->unsignedTinyInteger('drawn')->default(0);
            $table->unsignedTinyInteger('lost')->default(0);
            $table->unsignedSmallInteger('goals_for')->default(0);
            $table->unsignedSmallInteger('goals_against')->default(0);
            $table->smallInteger('goal_difference')->default(0);
            $table->unsignedSmallInteger('points')->default(0);
            $table->enum('zone', ['ucl', 'rel', 'none'])->default('none');
            $table->timestamps();

            $table->unique(['league_id', 'team_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('standings');
    }
};
