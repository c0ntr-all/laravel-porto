<?php declare(strict_types=1);

use App\Ship\Enums\FileSourceEnum;
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
        Schema::create('gallery_videos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('album_id');
            $table->enum('source', FileSourceEnum::toArray());
            $table->string('duration', 10)->nullable();
            $table->string('extension', 4)->nullable();
            $table->string('original_name')->nullable();
            $table->string('external_url')->nullable();
            $table->integer('width');
            $table->integer('height');
            $table->longText('description')
                  ->nullable()
                  ->default(NULL);
            $table->timestamps();

            $table->index('user_id');
            $table->index('album_id');
            $table->index(['user_id', 'album_id']);
            $table->index('created_at');

            $table->foreign('user_id')
                  ->references('id')
                  ->on('users');

            $table->foreign('album_id')
                  ->references('id')
                  ->on('gallery_albums');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gallery_videos');
    }
};
