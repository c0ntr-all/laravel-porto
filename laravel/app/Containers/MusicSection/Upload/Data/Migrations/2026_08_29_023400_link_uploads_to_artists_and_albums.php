<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('music_upload_artist', function (Blueprint $table) {
            $table->unsignedBigInteger('upload_id');
            $table->unsignedBigInteger('artist_id');
            $table->timestamps();

            $table->primary(['upload_id', 'artist_id']);

            $table->foreign('upload_id')
                  ->references('id')
                  ->on('music_uploads')
                  ->cascadeOnDelete();

            $table->foreign('artist_id')
                  ->references('id')
                  ->on('music_artists')
                  ->cascadeOnDelete();
        });

        Schema::create('music_upload_album', function (Blueprint $table) {
            $table->unsignedBigInteger('upload_id');
            $table->unsignedBigInteger('album_id');
            $table->timestamps();

            $table->primary(['upload_id', 'album_id']);

            $table->foreign('upload_id')
                  ->references('id')
                  ->on('music_uploads')
                  ->cascadeOnDelete();

            $table->foreign('album_id')
                  ->references('id')
                  ->on('music_albums')
                  ->cascadeOnDelete();
        });

        Schema::table('music_upload_tracks', function (Blueprint $table) {
            $table->foreignId('album_id')
                  ->nullable()
                  ->after('track_id')
                  ->constrained('music_albums')
                  ->nullOnDelete();
            $table->foreignId('artist_id')
                  ->nullable()
                  ->after('album_id')
                  ->constrained('music_artists')
                  ->nullOnDelete();
        });

        $this->backfill();
    }

    public function down(): void
    {
        Schema::table('music_upload_tracks', function (Blueprint $table) {
            $table->dropConstrainedForeignId('artist_id');
            $table->dropConstrainedForeignId('album_id');
        });

        Schema::dropIfExists('music_upload_album');
        Schema::dropIfExists('music_upload_artist');
    }

    private function backfill(): void
    {
        $now = now();

        DB::table('music_uploads')
            ->whereNotNull('artist_id')
            ->orderBy('id')
            ->chunkById(200, function ($uploads) use ($now) {
                $rows = [];
                foreach ($uploads as $upload) {
                    $rows[] = [
                        'upload_id' => $upload->id,
                        'artist_id' => $upload->artist_id,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                if ($rows !== []) {
                    DB::table('music_upload_artist')->insertOrIgnore($rows);
                }
            });

        DB::table('music_upload_tracks')
            ->whereNotNull('track_id')
            ->orderBy('id')
            ->chunkById(200, function ($logs) use ($now) {
                $trackIds = $logs->pluck('track_id')->unique()->all();
                $tracks = DB::table('music_tracks')
                    ->whereIn('id', $trackIds)
                    ->get(['id', 'album_id'])
                    ->keyBy('id');
                $artistsByTrack = DB::table('music_track_artist')
                    ->whereIn('track_id', $trackIds)
                    ->get()
                    ->groupBy('track_id');

                $albumPivot = [];
                foreach ($logs as $log) {
                    $track = $tracks->get($log->track_id);
                    $albumId = $track?->album_id;
                    $artistId = $artistsByTrack->get($log->track_id)?->first()?->artist_id;

                    DB::table('music_upload_tracks')->where('id', $log->id)->update([
                        'album_id' => $albumId,
                        'artist_id' => $artistId,
                    ]);

                    if ($albumId) {
                        $albumPivot[$log->upload_id . ':' . $albumId] = [
                            'upload_id' => $log->upload_id,
                            'album_id' => $albumId,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }
                }

                if ($albumPivot !== []) {
                    DB::table('music_upload_album')->insertOrIgnore(array_values($albumPivot));
                }
            });
    }
};
