<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Tag\UI\Actions;

use App\Containers\MusicSection\Tag\Models\MusicTag;
use App\Containers\MusicSection\Tag\Tasks\DeleteTagWithChildrenTask;
use App\Containers\MusicSection\Tag\UI\API\Requests\DeleteRequest;
use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteTagAction
{
    use AsAction;

    public function __construct(
        private readonly DeleteTagWithChildrenTask $deleteTagWithChildrenTask
    )
    {
    }

    public function handle(MusicTag $tag): int
    {
        return $this->deleteTagWithChildrenTask->run($tag);
    }

    public function asController(MusicTag $tag, DeleteRequest $request): JsonResponse
    {
        $tag->load('tags');

        $this->handle($tag);

        return response()->json(
            ['meta' => [
                'message' => 'Tag and all his nested tags successfully removed!'
            ]]
        );
    }
}
