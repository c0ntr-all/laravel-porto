<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tm_task_template_checklists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->foreignId('task_template_id')
                  ->constrained('tm_task_templates')
                  ->cascadeOnDelete();
            $table->string('title');
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();

            $table->foreign('user_id')
                  ->references('id')
                  ->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tm_task_template_checklists');
    }
};
