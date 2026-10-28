<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_imports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('game_id')->nullable();
            $table->unsignedBigInteger('steam_id');
            $table->string('source_url', 2048);
            $table->string('status', 32);
            $table->unsignedSmallInteger('http_status')->nullable();
            $table->boolean('was_created')->nullable();
            $table->json('parsed_payload')->nullable();
            $table->json('meta')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->unsignedInteger('duration_ms')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index('steam_id');
            $table->index('status');

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('game_id')->references('id')->on('games')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_imports');
    }
};
