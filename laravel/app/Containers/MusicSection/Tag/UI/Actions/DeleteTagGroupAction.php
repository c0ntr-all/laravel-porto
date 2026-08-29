<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Tag\UI\Actions;

use App\Containers\MusicSection\Tag\Models\MusicTagGroup;
use App\Containers\MusicSection\Tag\Tasks\DeleteTagGroupTask;
use App\Containers\MusicSection\Tag\UI\API\Requests\TagGroupDeleteRequest;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;

class DeleteTagGroupAction extends BaseAction
{
    public function __construct(
        private readonly DeleteTagGroupTask $deleteTagGroupTask
    )
    {
    }

    public function handle(MusicTagGroup $tagGroup): bool
    {
        return $this->deleteTagGroupTask->run($tagGroup);
    }

    public function asController(MusicTagGroup $tagGroup, TagGroupDeleteRequest $request): JsonResponse
    {
        $this->handle($tagGroup);

        return response()->json([
            'meta' => [
                'message' => 'Tag group successfully deleted!',
            ],
        ]);
    }
}
