<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movie_folders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('name', 150);
            $table->string('slug', 50)->nullable();
            $table->boolean('is_system')->default(false);
            $table->unsignedInteger('movies_count')->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'name']);
            $table->unique(['user_id', 'slug']);
            $table->index(['user_id', 'is_system']);
        });

        Schema::create('movie_folder_movie', function (Blueprint $table) {
            $table->id();
            $table->foreignId('folder_id')->constrained('movie_folders')->cascadeOnDelete();
            $table->foreignId('movie_id')->constrained('movies')->cascadeOnDelete();
            $table->timestamp('added_at')->useCurrent();
            $table->timestamps();

            $table->unique(['folder_id', 'movie_id']);
            $table->index(['folder_id', 'added_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movie_folder_movie');
        Schema::dropIfExists('movie_folders');
    }
};
