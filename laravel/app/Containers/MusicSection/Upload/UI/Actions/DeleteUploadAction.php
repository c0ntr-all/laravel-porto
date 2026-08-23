<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Upload\UI\Actions;

use App\Containers\MusicSection\Upload\Models\MusicUpload;
use App\Containers\MusicSection\Upload\Tasks\DeleteUploadTask;
use App\Containers\MusicSection\Upload\UI\API\Requests\DeleteRequest;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;

class DeleteUploadAction extends BaseAction
{
    public function __construct(
        private readonly DeleteUploadTask $deleteUploadTask
    ) {
    }

    public function handle(MusicUpload $upload): ?bool
    {
        return $this->deleteUploadTask->run($upload);
    }

    public function asController(MusicUpload $upload, DeleteRequest $request): JsonResponse
    {
        $this->handle($upload);

        return response()->json([
            'meta' => [
                'message' => 'Upload session successfully deleted!',
            ],
        ]);
    }
}
