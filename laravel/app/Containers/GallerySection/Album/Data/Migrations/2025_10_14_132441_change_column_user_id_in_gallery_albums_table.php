<?php declare(strict_types=1);

use App\Containers\GallerySection\Album\Enums\SystemAlbumsEnum;
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
        Schema::table('gallery_albums', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->change();
        });

        $now = DB::raw('CURRENT_TIMESTAMP');

        DB::table('gallery_albums')->insert(array_map(static function (array $item) use ($now) {
            return [...$item, 'created_at' => $now, 'updated_at' => $now];
        }, [[
                'system_code' => SystemAlbumsEnum::UPLOAD->value,
                'name' => 'Upload',
                'description' => 'An album for uploaded images',
            ],
            [
                'system_code' => SystemAlbumsEnum::SAVE->value,
                'name' => 'Save',
                'description' => 'An album for saved images',
            ]]
        ));
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('gallery_albums')->whereIn(
            'system_code',
            [
                SystemAlbumsEnum::UPLOAD->value,
                SystemAlbumsEnum::SAVE->value
            ]
        )->delete();

        Schema::table('gallery_albums', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
        });
    }
};
