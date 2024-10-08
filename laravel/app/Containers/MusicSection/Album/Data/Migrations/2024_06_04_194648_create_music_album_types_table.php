<?php

use App\Containers\MusicSection\Album\Enums\AlbumTypeEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('music_album_types', function (Blueprint $table) {
            $table->id();
            $table->string('slug');
            $table->string('name');
            $table->longText('description')->nullable()->default(NULL);
            $table->timestamps();
        });

        $types = AlbumTypeEnum::toArray();

        foreach ($types as $type) {
            DB::table('music_album_types')->insert([
                'name' => $type,
                'slug' => Str::slug($type)
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('music_album_types');
    }
};
