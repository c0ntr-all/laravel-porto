<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Upload\UI\Actions;

use App\Containers\MusicSection\Upload\Helpers\PathHelper;
use App\Containers\MusicSection\Upload\Tasks\PrepareUploadMusicTreeTask;
use App\Containers\MusicSection\Upload\Tasks\UploadMusicTask;
use App\Containers\MusicSection\Upload\UI\API\Requests\UploadRequest;
use Exception;
use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;

class UploadMusicAction
{
    use AsAction;

    public function __construct(
        private readonly PrepareUploadMusicTreeTask $prepareUploadingMusicTreeTask,
        private readonly UploadMusicTask $uploadMusicTask
    )
    {
    }

    /**
     * @throws Exception
     */
    public function handle(array $data): array
    {
        $musicData = $this->prepareUploadingMusicTreeTask->run($data['path']);

        if (!empty($data['is_preview'])) {
            return $musicData;
        } else {
            return $this->uploadMusicTask->run($musicData);
        }
    }

    /**
     * @param UploadRequest $request
     * @return JsonResponse
     * @throws Exception
     */
    public function asController(UploadRequest $request): JsonResponse
    {
        $linuxPath = PathHelper::windowsToLinux($request->input('path'));
        $data = $request->validated();
        $data['path'] = $linuxPath;
        $result = $this->handle($data);

        $artists = $result['artists'];

        $response = empty($request->is_preview) ? [
            'data' => $result,
            'meta' => [
                'message' => 'Artists ' . $artists->implode(',', 'name') . ' successfully uploaded!'
            ]
        ] : $result;

        return response()->json($response);
    }
}
