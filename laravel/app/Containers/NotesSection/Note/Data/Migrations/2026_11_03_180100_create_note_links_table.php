<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('note_links', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('source_note_id');
            $table->unsignedBigInteger('target_note_id');
            $table->string('label', 255);
            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();

            $table->foreign('source_note_id')
                ->references('id')
                ->on('notes')
                ->cascadeOnDelete();

            $table->foreign('target_note_id')
                ->references('id')
                ->on('notes')
                ->cascadeOnDelete();

            $table->unique(['source_note_id', 'target_note_id']);
            $table->index('target_note_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('note_links');
    }
};
