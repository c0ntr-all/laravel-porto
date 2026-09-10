<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('custom_fields', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('user_id');
            $table->string('type', 32);
            $table->string('fieldable_type');
            $table->string('fieldable_id', 36);
            $table->json('payload');
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();

            $table->index(['fieldable_type', 'fieldable_id']);
            $table->index(['user_id', 'fieldable_type', 'fieldable_id']);

            $table->foreign('user_id')
                ->references('id')
                ->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_fields');
    }
};
