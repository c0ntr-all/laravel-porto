<?php declare(strict_types=1);

namespace App\Ship\Tasks;

use App\Ship\Data\DTO\PersistRemoteImageData;
use App\Ship\Helpers\ImageUpload;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class PersistRemoteImageTask extends ParentTask
{
    private const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

    /**
     * Downloads a remote image and stores it on the given disk.
     *
     * @return string Relative path on the disk
     */
    public function run(PersistRemoteImageData $dto): string
    {
        $url = trim($dto->url);
        if ($url === '') {
            throw new RuntimeException('Remote image URL is empty.');
        }

        try {
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (compatible; HomePortal/1.0)',
                'Accept' => 'image/*,*/*;q=0.8',
            ])
                ->withOptions(['verify' => false])
                ->timeout(60)
                ->get($url)
                ->throw();
        } catch (RequestException $exception) {
            throw new RuntimeException(
                'Failed to download remote image: '.$url,
                previous: $exception,
            );
        }

        $contents = $response->body();
        if ($contents === '') {
            throw new RuntimeException('Remote image response is empty: '.$url);
        }

        $extension = $this->resolveExtension($url, $response->header('Content-Type'));
        $filename = $dto->filename !== null && $dto->filename !== ''
            ? $this->normalizeFilename($dto->filename, $extension)
            : 'image.'.$extension;

        $directory = trim($dto->directory, '/');
        if ($directory === '') {
            throw new RuntimeException('Storage directory must not be empty.');
        }

        $path = ImageUpload::make($filename, $directory, $dto->disk)->upload($contents);

        if (!Storage::disk($dto->disk)->exists($path)) {
            throw new RuntimeException('Image was not written to disk: '.$path);
        }

        return $path;
    }

    private function resolveExtension(string $url, ?string $contentType): string
    {
        if (is_string($contentType) && $contentType !== '') {
            $mime = strtolower(trim(explode(';', $contentType, 2)[0]));
            $fromMime = match ($mime) {
                'image/jpeg', 'image/jpg', 'image/pjpeg' => 'jpg',
                'image/png' => 'png',
                'image/webp' => 'webp',
                'image/gif' => 'gif',
                default => null,
            };

            if ($fromMime !== null) {
                return $fromMime;
            }
        }

        $path = parse_url($url, PHP_URL_PATH);
        $fromUrl = is_string($path) ? strtolower((string) pathinfo($path, PATHINFO_EXTENSION)) : '';
        if ($fromUrl === 'jpeg') {
            return 'jpg';
        }

        if (in_array($fromUrl, self::ALLOWED_EXTENSIONS, true)) {
            return $fromUrl;
        }

        return 'jpg';
    }

    private function normalizeFilename(string $filename, string $extension): string
    {
        $basename = pathinfo($filename, PATHINFO_FILENAME);
        $basename = $basename !== '' ? $basename : 'image';
        $givenExt = strtolower((string) pathinfo($filename, PATHINFO_EXTENSION));

        if ($givenExt === 'jpeg') {
            $givenExt = 'jpg';
        }

        if (in_array($givenExt, self::ALLOWED_EXTENSIONS, true)) {
            return $basename.'.'.$givenExt;
        }

        return $basename.'.'.$extension;
    }
}
