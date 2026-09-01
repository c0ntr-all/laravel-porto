<?php declare(strict_types=1);

namespace App\Containers\AppSection\Attachment\Tasks;

use App\Containers\AppSection\Attachment\Data\DTO\FileableReferenceDto;
use App\Containers\AppSection\Attachment\Services\FileableUploaderRegistry;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Http\UploadedFile;

class ResolveFileableFromUploadTask extends ParentTask
{
    public function __construct(
        private readonly FileableUploaderRegistry $fileableUploaderRegistry,
    ) {
    }

    public function run(UploadedFile $file, int $userId): FileableReferenceDto
    {
        $uploader = $this->fileableUploaderRegistry->resolve($file);

        return $uploader->upload($file, $userId);
    }
}
