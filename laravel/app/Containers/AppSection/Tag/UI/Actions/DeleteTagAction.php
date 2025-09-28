<?php declare(strict_types=1);

namespace App\Containers\AppSection\Tag\UI\Actions;

use App\Containers\AppSection\Tag\Models\Tag;
use App\Containers\AppSection\Tag\Tasks\DeleteTagTask;
use App\Containers\AppSection\Tag\UI\API\Requests\DeleteRequest;
use App\Ship\Exceptions\DeleteResourceFailedException;
use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteTagAction
{
    use AsAction;

    public function __construct(
        private readonly DeleteTagTask $deleteTagWithChildrenTask
    )
    {
    }

    /**
     * @throws DeleteResourceFailedException
     */
    public function handle(Tag $tag): int
    {
        return $this->deleteTagWithChildrenTask->run($tag);
    }

    /**
     * @throws DeleteResourceFailedException
     */
    public function asController(Tag $tag, DeleteRequest $request): JsonResponse
    {
        $tag->load('tags');

        $this->handle($tag);

        return response()->json(
            ['meta' => [
                'message' => 'Tag successfully removed!'
            ]]
        );
    }
}
