<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movie_season_watches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('season_id')->constrained('movie_seasons')->cascadeOnDelete();
            $table->timestamp('watched_at')->useCurrent();
            $table->timestamps();

            $table->unique(['user_id', 'season_id']);
            $table->index(['season_id', 'watched_at']);
        });

        Schema::create('movie_episode_watches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('episode_id')->constrained('movie_episodes')->cascadeOnDelete();
            $table->timestamp('watched_at')->useCurrent();
            $table->timestamps();

            $table->unique(['user_id', 'episode_id']);
            $table->index(['episode_id', 'watched_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movie_episode_watches');
        Schema::dropIfExists('movie_season_watches');
    }
};
