<?php declare(strict_types=1);

namespace App\Containers\AppSection\Document\Tasks;

use App\Containers\AppSection\Document\Data\DTO\CreateDocumentDto;
use App\Containers\AppSection\Document\Data\Repositories\DocumentRepository;
use App\Containers\AppSection\Document\Models\Document;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Ramsey\Uuid\Uuid;

class SaveUploadedDocumentTask extends ParentTask
{
    public function run(UploadedFile $file, int $userId): array
    {
        $uuid = Uuid::uuid4()->toString();
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
            'id' => $uuid,
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
