<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Movie\Jobs;

use App\Containers\MovieSection\Movie\Models\Movie;
use App\Containers\MovieSection\Movie\Support\KinopoiskMovieImagePath;
use App\Containers\MovieSection\Movie\Tasks\PersistMovieKinopoiskImageTask;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Sequentially downloads Kinopoisk posters for movies into local storage.
 * Progress is cached so a restarted run can continue after the last processed id.
 */
class SyncMovieKinopoiskImagesJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public const CACHE_LAST_ID = 'movie:kinopoisk_images:last_id';

    public const CACHE_STATUS = 'movie:kinopoisk_images:status';

    public const CACHE_STATS = 'movie:kinopoisk_images:stats';

    public int $tries = 1;

    public int $timeout = 3600;

    public function __construct(
        public readonly ?int $fromId = null,
        public readonly bool $force = false,
        public readonly ?int $limit = null,
        public readonly bool $resetCursor = false,
    ) {
    }

    public function handle(PersistMovieKinopoiskImageTask $persistMovieKinopoiskImageTask): void
    {
        if ($this->resetCursor) {
            Cache::forget(self::CACHE_LAST_ID);
            Cache::forget(self::CACHE_STATS);
        }

        $afterId = $this->fromId ?? (int) Cache::get(self::CACHE_LAST_ID, 0);

        $stats = [
            'started_after_id' => $afterId,
            'processed' => 0,
            'succeeded' => 0,
            'failed' => 0,
            'skipped' => 0,
            'last_id' => $afterId,
        ];

        Cache::put(self::CACHE_STATUS, 'running');
        Cache::put(self::CACHE_STATS, $stats);

        Log::info('movie.kinopoisk_images.sync.started', [
            'after_id' => $afterId,
            'force' => $this->force,
            'limit' => $this->limit,
        ]);

        while ($this->limit === null || $stats['processed'] < $this->limit) {
            $movie = $this->nextMovie($afterId);
            if ($movie === null) {
                break;
            }

            $afterId = (int) $movie->id;
            $stats['last_id'] = $afterId;
            $stats['processed']++;

            // Persist cursor before download so a crash mid-download resumes after this id.
            Cache::put(self::CACHE_LAST_ID, $afterId);

            $beforeImg = is_string($movie->img) ? $movie->img : null;
            $source = is_string($movie->kp_img) ? $movie->kp_img : null;

            Log::info('movie.kinopoisk_images.sync.processing', [
                'movie_id' => $afterId,
                'kp_id' => $movie->kp_id,
                'title' => $movie->title,
                'kp_img' => $source,
                'had_img' => $beforeImg !== null && $beforeImg !== '',
                'force' => $this->force,
            ]);

            try {
                $updated = $persistMovieKinopoiskImageTask->run($movie, force: $this->force);
                $afterImg = is_string($updated->img) ? $updated->img : null;

                if ($afterImg !== null && $afterImg !== '' && $afterImg !== $beforeImg) {
                    $stats['succeeded']++;
                } else {
                    $stats['skipped']++;
                }
            } catch (Throwable $exception) {
                $stats['failed']++;

                Log::error('movie.kinopoisk_images.sync.item_failed', [
                    'movie_id' => $afterId,
                    'kp_id' => $movie->kp_id,
                    'kp_img' => $source,
                    'error' => $exception->getMessage(),
                ]);
            }

            Cache::put(self::CACHE_STATS, $stats);
        }

        Cache::put(self::CACHE_STATUS, 'completed');
        Cache::put(self::CACHE_STATS, $stats);

        Log::info('movie.kinopoisk_images.sync.completed', $stats);
    }

    private function nextMovie(int $afterId): ?Movie
    {
        $query = Movie::query()
            ->where('id', '>', $afterId)
            ->whereNotNull(KinopoiskMovieImagePath::SOURCE_ATTRIBUTE)
            ->where(KinopoiskMovieImagePath::SOURCE_ATTRIBUTE, '!=', '')
            ->orderBy('id');

        if (!$this->force) {
            $query->where(function ($builder): void {
                $builder->whereNull(KinopoiskMovieImagePath::TARGET_ATTRIBUTE)
                    ->orWhere(KinopoiskMovieImagePath::TARGET_ATTRIBUTE, '=', '');
            });
        }

        return $query->first();
    }
}
