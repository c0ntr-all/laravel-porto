<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gallery_images', function (Blueprint $table) {
            $table->uuid('saved_from_id')->nullable()->after('album_id');
            $table->index(['user_id', 'album_id', 'saved_from_id']);
        });

        Schema::table('gallery_videos', function (Blueprint $table) {
            $table->uuid('saved_from_id')->nullable()->after('album_id');
            $table->index(['user_id', 'album_id', 'saved_from_id']);
        });
    }

    public function down(): void
    {
        Schema::table('gallery_images', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'album_id', 'saved_from_id']);
            $table->dropColumn('saved_from_id');
        });

        Schema::table('gallery_videos', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'album_id', 'saved_from_id']);
            $table->dropColumn('saved_from_id');
        });
    }
};
