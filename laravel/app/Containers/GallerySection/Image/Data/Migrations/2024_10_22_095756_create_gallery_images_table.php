<?php declare(strict_types=1);

use App\Containers\GallerySection\Image\Enums\ImageSourceEnum;
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
        Schema::create('gallery_images', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('album_id');
            $table->enum('source', ImageSourceEnum::toArray());
            $table->string('original_path');
            $table->string('list_thumb_path');
            $table->string('preview_thumb_path');
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
        Schema::dropIfExists('gallery_images');
    }
};
