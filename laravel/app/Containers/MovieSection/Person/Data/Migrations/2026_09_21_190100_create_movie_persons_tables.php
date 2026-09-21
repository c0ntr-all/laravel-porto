<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movie_persons', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('kp_id')->unique();
            $table->foreignId('profession_id')->nullable()->constrained('movie_professions')->nullOnDelete();
            $table->string('name');
            $table->string('en_name')->nullable();
            $table->string('photo', 2048)->nullable();
            $table->timestamps();

            $table->index('profession_id');
            $table->index('name');
        });

        Schema::create('movie_person_profession', function (Blueprint $table) {
            $table->id();
            $table->foreignId('person_id')->constrained('movie_persons')->cascadeOnDelete();
            $table->foreignId('profession_id')->constrained('movie_professions')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['person_id', 'profession_id']);
        });

        Schema::create('movie_person', function (Blueprint $table) {
            $table->id();
            $table->foreignId('movie_id')->constrained('movies')->cascadeOnDelete();
            $table->foreignId('person_id')->constrained('movie_persons')->cascadeOnDelete();
            $table->foreignId('profession_id')->constrained('movie_professions')->cascadeOnDelete();
            $table->string('description', 500)->nullable();
            $table->timestamps();

            $table->unique(['movie_id', 'person_id', 'profession_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movie_person');
        Schema::dropIfExists('movie_person_profession');
        Schema::dropIfExists('movie_persons');
    }
};
