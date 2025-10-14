<?php declare(strict_types=1);

use App\Containers\GallerySection\Album\Enums\SystemAlbumsEnum;
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
        Schema::table('gallery_albums', function (Blueprint $table) {
            $table->enum('system_code', SystemAlbumsEnum::toArray())->nullable()->after('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gallery_albums', function (Blueprint $table) {
            $table->dropColumn('system_code');
        });
    }
};
