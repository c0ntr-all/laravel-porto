<?php declare(strict_types=1);

namespace App\Containers\AppSection\Document\Tasks;

use App\Ship\Helpers\UuidV7;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class SaveUploadedDocumentTask extends ParentTask
{
    public function run(UploadedFile $file, int $userId): array
    {
        $uuid = UuidV7::generate();
        $extension = strtolower($file->getClientOriginalExtension());
        $path = str_replace(
            ['{user_id}', '{file_id}', '{extension}'],
            [(string) $userId, $uuid, $extension],
            (string) config('document.path_mask')
        );
        $disk = (string) config('document.disk', 'public');

        Storage::disk($disk)->putFileAs(
            dirname($path),
            $file,
            basename($path)
        );

        return [
            'uuid' => $uuid,
            'user_id' => $userId,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => (string) ($file->getMimeType() ?: 'application/octet-stream'),
            'extension' => $extension,
            'size' => (int) $file->getSize(),
            'disk' => $disk,
            'path' => $path,
        ];
    }
}
