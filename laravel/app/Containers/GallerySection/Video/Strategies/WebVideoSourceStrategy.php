<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Video\Strategies;

use App\Containers\GallerySection\Video\Contracts\VideoSourceContract;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class WebVideoSourceStrategy implements VideoSourceContract
{
    private ?string $tempRelativePath = null;

    public function __construct(
        private readonly string $url,
    ) {
    }

    public function getDisk(): string
    {
        return (string) config('video.disk', 'public');
    }

    public function getRelativePath(): string
    {
        if ($this->tempRelativePath !== null) {
            return $this->tempRelativePath;
        }

        $extension = $this->getExtension() ?: 'mp4';
        $this->tempRelativePath = 'tmp/gallery-videos/' . uniqid('web_', true) . '.' . $extension;
        $disk = Storage::disk($this->getDisk());
        $disk->makeDirectory('tmp/gallery-videos');

        $response = Http::timeout(60)
            ->withHeaders([
                'User-Agent' => 'Mozilla/5.0',
            ])
            ->withOptions(['verify' => false])
            ->get($this->url);

        if (!$response->successful()) {
            throw new \RuntimeException('Unable to download remote video.');
        }

        $disk->put($this->tempRelativePath, $response->body());

        return $this->tempRelativePath;
    }

    public function getExtension(): string
    {
        $path = parse_url($this->url, PHP_URL_PATH) ?: $this->url;

        return strtolower(pathinfo((string) $path, PATHINFO_EXTENSION));
    }

    public function getOriginalName(): string
    {
        $path = parse_url($this->url, PHP_URL_PATH) ?: $this->url;

        return basename((string) $path) ?: 'remote-video';
    }

    public function getStoredExternalUrl(): ?string
    {
        return $this->url;
    }

    public function cleanup(): void
    {
        if ($this->tempRelativePath === null) {
            return;
        }

        $disk = Storage::disk($this->getDisk());
        if ($disk->exists($this->tempRelativePath)) {
            $disk->delete($this->tempRelativePath);
        }
    }
}
