<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movie_episodes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('season_id')->constrained('movie_seasons')->cascadeOnDelete();
            $table->unsignedInteger('kp_id')->nullable()->unique();
            $table->unsignedInteger('kp_season_id')->nullable();
            $table->string('name', 255)->nullable();
            $table->text('description')->nullable();
            $table->text('en_description')->nullable();
            $table->unsignedSmallInteger('number');
            $table->unsignedInteger('duration')->nullable();
            $table->date('air_date')->nullable();
            $table->string('still', 2048)->nullable();
            $table->string('still_preview', 2048)->nullable();
            $table->timestamps();

            $table->unique(['season_id', 'number']);
            $table->index(['season_id', 'number']);
            $table->index('kp_season_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movie_episodes');
    }
};
