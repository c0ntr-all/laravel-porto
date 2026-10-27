<?php declare(strict_types=1);

namespace App\Ship\Jobs;

use App\Ship\Data\DTO\PersistRemoteImageData;
use App\Ship\Tasks\PersistRemoteImageTask;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Universal job: download a remote image from a model attribute and persist the local path
 * into another attribute.
 */
class PersistRemoteImageJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public int $timeout = 120;

    /**
     * @param class-string<Model> $modelClass
     */
    public function __construct(
        public readonly string $modelClass,
        public readonly int|string $modelId,
        public readonly string $sourceAttribute,
        public readonly string $targetAttribute,
        public readonly string $directory,
        public readonly string $disk = 'public',
        public readonly ?string $filename = null,
        public readonly bool $force = false,
        public readonly string $logContext = 'ship.persist_remote_image',
    ) {
    }

    public function handle(PersistRemoteImageTask $persistRemoteImageTask): void
    {
        /** @var Model|null $model */
        $model = $this->modelClass::query()->find($this->modelId);
        if ($model === null) {
            Log::warning($this->logContext.'.skipped', [
                'reason' => 'model_not_found',
                'model' => $this->modelClass,
                'model_id' => $this->modelId,
            ]);

            return;
        }

        $url = $model->getAttribute($this->sourceAttribute);
        if (!is_string($url) || trim($url) === '') {
            Log::info($this->logContext.'.skipped', [
                'reason' => 'empty_source',
                'model' => $this->modelClass,
                'model_id' => $this->modelId,
                'source_attribute' => $this->sourceAttribute,
            ]);

            return;
        }

        $currentPath = $model->getAttribute($this->targetAttribute);
        if (
            !$this->force
            && is_string($currentPath)
            && $currentPath !== ''
            && Storage::disk($this->disk)->exists($currentPath)
        ) {
            Log::info($this->logContext.'.skipped', [
                'reason' => 'target_already_set',
                'model' => $this->modelClass,
                'model_id' => $this->modelId,
                'target_attribute' => $this->targetAttribute,
                'path' => $currentPath,
            ]);

            return;
        }

        try {
            $path = $persistRemoteImageTask->run(PersistRemoteImageData::from([
                'url' => trim($url),
                'directory' => $this->directory,
                'disk' => $this->disk,
                'filename' => $this->filename,
            ]));

            if (is_string($currentPath) && $currentPath !== '' && $currentPath !== $path) {
                Storage::disk($this->disk)->delete($currentPath);
            }

            $model->setAttribute($this->targetAttribute, $path);
            $model->save();

            Log::info($this->logContext.'.success', [
                'model' => $this->modelClass,
                'model_id' => $this->modelId,
                'source_attribute' => $this->sourceAttribute,
                'target_attribute' => $this->targetAttribute,
                'source_url' => $url,
                'path' => $path,
                'disk' => $this->disk,
            ]);
        } catch (Throwable $exception) {
            Log::error($this->logContext.'.failed', [
                'model' => $this->modelClass,
                'model_id' => $this->modelId,
                'source_attribute' => $this->sourceAttribute,
                'target_attribute' => $this->targetAttribute,
                'source_url' => $url,
                'directory' => $this->directory,
                'disk' => $this->disk,
                'error' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }
}
