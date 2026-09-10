<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('music_uploads', function (Blueprint $table) {
            $table->string('artist_name')->nullable()->after('user_id');
        });

        Schema::table('music_upload_tracks', function (Blueprint $table) {
            $table->string('artist_name')->nullable()->after('artist_id');
        });

        $this->backfill();
    }

    public function down(): void
    {
        Schema::table('music_upload_tracks', function (Blueprint $table) {
            $table->dropColumn('artist_name');
        });

        Schema::table('music_uploads', function (Blueprint $table) {
            $table->dropColumn('artist_name');
        });
    }

    private function backfill(): void
    {
        DB::table('music_uploads')
            ->orderBy('id')
            ->chunkById(100, function ($uploads): void {
                foreach ($uploads as $upload) {
                    $this->backfillUpload((int) $upload->id, $upload->meta);
                }
            });
    }

    private function backfillUpload(int $uploadId, mixed $rawMeta): void
    {
        $logs = DB::table('music_upload_tracks')
            ->where('upload_id', $uploadId)
            ->orderBy('id')
            ->get();

        $imported = [];
        $seen = [];

        foreach ($logs as $log) {
            $snapshot = $this->decodeJson($log->snapshot);
            $importedName = trim((string) ($snapshot['artist'] ?? ''));
            if ($importedName === '') {
                continue;
            }

            $key = mb_strtolower($importedName);
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $imported[] = [
                'id' => $log->artist_id !== null ? (int) $log->artist_id : null,
                'name' => $importedName,
            ];
        }

        if ($imported === []) {
            $artistIds = DB::table('music_upload_artist')
                ->where('upload_id', $uploadId)
                ->pluck('artist_id')
                ->all();
            $artists = DB::table('music_artists')
                ->whereIn('id', $artistIds)
                ->get(['id', 'name']);

            foreach ($artists as $artist) {
                $imported[] = [
                    'id' => (int) $artist->id,
                    'name' => (string) $artist->name,
                ];
            }
        }

        foreach ($logs as $log) {
            $snapshot = $this->decodeJson($log->snapshot);
            $artistName = trim((string) ($snapshot['artist'] ?? ''));
            if ($artistName === '' && $log->artist_id) {
                $artistName = (string) (DB::table('music_artists')->where('id', $log->artist_id)->value('name') ?? '');
            }

            if ($artistName !== '') {
                DB::table('music_upload_tracks')->where('id', $log->id)->update([
                    'artist_name' => $artistName,
                ]);
            }
        }

        $meta = $this->decodeJson($rawMeta);
        $meta['imported_artists'] = $imported;
        $artistName = collect($imported)->pluck('name')->filter()->implode(' / ');

        DB::table('music_uploads')->where('id', $uploadId)->update([
            'artist_name' => $artistName !== '' ? $artistName : null,
            'meta' => json_encode($meta, JSON_UNESCAPED_UNICODE),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeJson(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (!is_string($value) || trim($value) === '') {
            return [];
        }

        $decoded = json_decode($value, true);

        return is_array($decoded) ? $decoded : [];
    }
};
