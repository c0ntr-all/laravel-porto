<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movie_franchises', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['order']);
            $table->index(['user_id']);
        });

        Schema::create('movie_franchise_movie', function (Blueprint $table) {
            $table->id();
            $table->foreignId('franchise_id')->constrained('movie_franchises')->cascadeOnDelete();
            $table->foreignId('movie_id')->constrained('movies')->cascadeOnDelete();
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();

            $table->unique(['franchise_id', 'movie_id']);
            $table->index(['franchise_id', 'order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movie_franchise_movie');
        Schema::dropIfExists('movie_franchises');
    }
};
