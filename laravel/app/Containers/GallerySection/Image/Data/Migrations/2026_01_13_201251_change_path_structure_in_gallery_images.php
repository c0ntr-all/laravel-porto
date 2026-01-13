<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('gallery_images', function (Blueprint $table) {
            $table->string('extension', 4)->nullable()->after('source');
            $table->string('external_url')->nullable()->after('extension');
        });
        DB::table('gallery_images')->get()->each(function ($image) {
            $path = $image->original_path;
            if (is_string($path)) {
                $basename = basename($path);
                $parts = explode('.', $basename);
                $extension = array_pop($parts);

                DB::table('gallery_images')->where('id', $image->id)->update([
                    'extension' => $extension
                ]);
            }
        });
        Schema::table('gallery_images', function (Blueprint $table) {
            $table->dropColumn('original_path');
            $table->dropColumn('list_thumb_path');
            $table->dropColumn('preview_thumb_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gallery_images', function (Blueprint $table) {
            $table->string('original_path')->after('source');
            $table->string('list_thumb_path')->after('original_path');
            $table->string('preview_thumb_path')->after('list_thumb_path');
        });
        DB::table('gallery_images')->get()->each(function ($image) {
            $search = ['{user_id}', '{album_id}', '{file_id}', '{ext}'];
            $replace = [$image->user_id, $image->album_id, $image->id, $image->extension];
            $originalPathMask = 'userfiles/{user_id}/gallery/{album_id}/{file_id}.{ext}';
            $listThumbPathMask = 'userfiles/{user_id}/gallery/{album_id}/thumbnails/{file_id}_list_thumbnail.{ext}';
            $previewThumbPathMask = 'userfiles/{user_id}/gallery/{album_id}/thumbnails/{file_id}_preview_thumbnail.{ext}';
            $originalPath = str_replace($search, $replace, $originalPathMask);
            $listThumbnailPath = str_replace($search, $replace, $listThumbPathMask);
            $previewThumbPath = str_replace($search, $replace, $previewThumbPathMask);

            DB::table('gallery_images')->where('id', $image->id)->update([
                'original_path' => $originalPath,
                'list_thumb_path' => $listThumbnailPath,
                'preview_thumb_path' => $previewThumbPath,
            ]);
        });
        Schema::table('gallery_images', function (Blueprint $table) {
            $table->dropColumn('extension');
            $table->dropColumn('external_url');
        });
    }
};
