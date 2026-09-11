<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movies', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('kp_id')->unique();
            $table->string('title');
            $table->unsignedSmallInteger('year');
            $table->string('type', 32);
            $table->string('cover', 2048)->nullable();
            $table->decimal('kp_rating', 3, 1)->nullable();
            $table->string('kp_img', 2048)->nullable();
            $table->timestamps();

            $table->index('year');
            $table->index('type');
            $table->index('title');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movies');
    }
};
