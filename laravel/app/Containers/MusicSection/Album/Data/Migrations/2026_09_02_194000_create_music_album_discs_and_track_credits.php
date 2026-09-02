<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('music_album_discs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('album_id');
            $table->unsignedInteger('number');
            $table->string('name')->nullable();
            $table->timestamps();

            $table->unique(['album_id', 'number']);
            $table->foreign('album_id')
                  ->references('id')
                  ->on('music_albums')
                  ->cascadeOnDelete();
        });

        Schema::table('music_tracks', function (Blueprint $table) {
            $table->unsignedBigInteger('disc_id')->nullable()->after('album_id');
            $table->text('credits')->nullable()->after('name');

            $table->foreign('disc_id')
                  ->references('id')
                  ->on('music_album_discs')
                  ->nullOnDelete();
        });

        $pairs = DB::table('music_tracks')
            ->select('album_id', 'cd')
            ->whereNotNull('album_id')
            ->whereNotNull('cd')
            ->where('cd', '!=', '')
            ->distinct()
            ->get();

        $now = now();

        foreach ($pairs as $pair) {
            $number = (int) $pair->cd;
            if ($number < 1) {
                $number = 1;
            }

            $discId = DB::table('music_album_discs')
                ->where('album_id', $pair->album_id)
                ->where('number', $number)
                ->value('id');

            if ($discId === null) {
                $discId = DB::table('music_album_discs')->insertGetId([
                    'album_id' => $pair->album_id,
                    'number' => $number,
                    'name' => 'CD '.$number,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            DB::table('music_tracks')
                ->where('album_id', $pair->album_id)
                ->where('cd', $pair->cd)
                ->update(['disc_id' => $discId]);
        }
    }

    public function down(): void
    {
        Schema::table('music_tracks', function (Blueprint $table) {
            $table->dropForeign(['disc_id']);
            $table->dropColumn(['disc_id', 'credits']);
        });

        Schema::dropIfExists('music_album_discs');
    }
};
