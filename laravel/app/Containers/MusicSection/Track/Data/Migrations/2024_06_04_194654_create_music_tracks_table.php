<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('music_tracks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('album_id')->nullable()->default(NULL);
            $table->integer('number')->nullable()->default(NULL);
            $table->string('name');
            $table->string('cd', 10)->nullable()->default(NULL);
            $table->string('path')->nullable()->default(NULL);
            $table->string('image')->nullable()->default(NULL);
            $table->string('duration')->nullable()->default(NULL);
            $table->integer('bitrate')->nullable()->default(NULL);
            $table->string('link')->nullable()->default(NULL);
            $table->longText('lyrics')->nullable()->default(NULL);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('album_id')
                  ->references('id')
                  ->on('music_albums')
                  ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('music_tracks');
    }
};
