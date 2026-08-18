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
        Schema::create('lifelog_presets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->datetime('start_date')->nullable()->default(NULL);
            $table->datetime('end_date')->nullable()->default(NULL);
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('color', 7)->nullable();
            $table->string('icon')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lifelog_presets');
    }
};
