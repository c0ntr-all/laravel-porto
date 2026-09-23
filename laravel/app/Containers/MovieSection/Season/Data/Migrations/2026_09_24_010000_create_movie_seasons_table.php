<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movie_seasons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('movie_id')->constrained('movies')->cascadeOnDelete();
            $table->unsignedInteger('kp_id')->nullable()->unique();
            $table->unsignedInteger('kp_season_id')->nullable()->unique();
            $table->unsignedInteger('kp_movie_id')->nullable();
            $table->string('name', 255)->nullable();
            $table->string('en_name', 255)->nullable();
            $table->unsignedSmallInteger('number');
            $table->date('air_date')->nullable();
            $table->unsignedSmallInteger('episodes_count')->nullable();
            $table->unsignedInteger('duration')->nullable();
            $table->string('poster', 2048)->nullable();
            $table->string('poster_preview', 2048)->nullable();
            $table->timestamps();

            $table->unique(['movie_id', 'number']);
            $table->index(['movie_id', 'number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movie_seasons');
    }
};
