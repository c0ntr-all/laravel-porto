<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gallery_album_image', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('album_id');
            $table->unsignedBigInteger('image_id');
            $table->timestamps();

            $table->unique(['album_id', 'image_id']);
            $table->index('image_id');

            $table->foreign('album_id')
                ->references('id')
                ->on('gallery_albums')
                ->cascadeOnDelete();
            $table->foreign('image_id')
                ->references('id')
                ->on('gallery_images')
                ->cascadeOnDelete();
        });

        Schema::table('gallery_images', function (Blueprint $table) {
            $table->string('thumb_status', 16)->default('ready')->after('description');
            $table->unsignedBigInteger('file_size')->nullable()->after('thumb_status');
            $table->unsignedInteger('file_mtime')->nullable()->after('file_size');
            $table->dateTime('taken_at')->nullable()->after('file_mtime');
            $table->boolean('file_missing')->default(false)->after('taken_at');
        });

        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql') {
            DB::statement(
                'ALTER TABLE gallery_images ADD INDEX gallery_images_external_url_prefix (external_url(191))'
            );
        } else {
            Schema::table('gallery_images', function (Blueprint $table) {
                $table->index('external_url', 'gallery_images_external_url_prefix');
            });
        }

        $now = now();
        DB::table('gallery_images')
            ->select(['id', 'album_id'])
            ->orderBy('id')
            ->chunkById(500, function ($images) use ($now): void {
                $rows = [];
                foreach ($images as $image) {
                    if ($image->album_id === null) {
                        continue;
                    }

                    $rows[] = [
                        'album_id' => $image->album_id,
                        'image_id' => $image->id,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                if ($rows !== []) {
                    DB::table('gallery_album_image')->insert($rows);
                }
            });
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE gallery_images DROP INDEX gallery_images_external_url_prefix');
        } else {
            Schema::table('gallery_images', function (Blueprint $table) {
                $table->dropIndex('gallery_images_external_url_prefix');
            });
        }

        Schema::table('gallery_images', function (Blueprint $table) {
            $table->dropColumn(['thumb_status', 'file_size', 'file_mtime', 'taken_at', 'file_missing']);
        });

        Schema::dropIfExists('gallery_album_image');
    }
};
