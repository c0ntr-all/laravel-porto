<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Movie\UI\CLI\Commands;

use App\Containers\MovieSection\Movie\Jobs\SyncMovieKinopoiskImagesJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class SyncMovieKinopoiskImagesCommand extends Command
{
    protected $signature = 'movie:sync-kinopoisk-images
                            {--from-id= : Resume after this movie id (overrides saved cursor)}
                            {--reset : Ignore saved cursor and start from the beginning}
                            {--force : Re-download even when img is already set}
                            {--limit= : Max movies to process in this run}
                            {--sync : Run inline instead of queueing}';

    protected $description = 'Download Kinopoisk posters (kp_img) into local storage (img) for all movies';

    public function handle(): int
    {
        $fromIdOption = $this->option('from-id');
        $fromId = $fromIdOption !== null && $fromIdOption !== ''
            ? max(0, (int) $fromIdOption)
            : null;

        $limitOption = $this->option('limit');
        $limit = $limitOption !== null && $limitOption !== ''
            ? max(1, (int) $limitOption)
            : null;

        $force = (bool) $this->option('force');
        $reset = (bool) $this->option('reset');

        $job = new SyncMovieKinopoiskImagesJob(
            fromId: $fromId,
            force: $force,
            limit: $limit,
            resetCursor: $reset,
        );

        if ((bool) $this->option('sync')) {
            dispatch_sync($job);
        } else {
            dispatch($job);
        }

        $this->info('Kinopoisk movie image sync '.((bool) $this->option('sync') ? 'finished' : 'queued').'.');
        $this->line('Cursor cache key: '.SyncMovieKinopoiskImagesJob::CACHE_LAST_ID);
        $this->line('Status: '.(string) Cache::get(SyncMovieKinopoiskImagesJob::CACHE_STATUS, 'n/a'));

        $stats = Cache::get(SyncMovieKinopoiskImagesJob::CACHE_STATS);
        if (is_array($stats)) {
            $this->table(array_keys($stats), [array_values($stats)]);
        }

        return self::SUCCESS;
    }
}
