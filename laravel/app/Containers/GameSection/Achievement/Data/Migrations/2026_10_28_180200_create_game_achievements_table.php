<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_achievements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained('games')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('steam_api_name');
            $table->timestamps();

            $table->unique(['game_id', 'steam_api_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_achievements');
    }
};
