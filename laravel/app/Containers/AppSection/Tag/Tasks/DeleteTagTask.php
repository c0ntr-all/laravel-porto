<?php declare(strict_types=1);

namespace App\Containers\AppSection\Tag\Tasks;

use App\Containers\AppSection\Tag\Data\Repositories\TagRepository;
use App\Containers\AppSection\Tag\Models\Tag;
use App\Ship\Exceptions\DeleteResourceFailedException;
use App\Ship\Parents\Tasks\Task;
use Illuminate\Support\Facades\DB;

class DeleteTagTask extends Task
{
    public function __construct(
        private readonly TagRepository $tagRepository,
    ) {
    }

    /**
     * @throws DeleteResourceFailedException
     */
    public function run(Tag $tag): int
    {
        try {
            $id = $tag->id;

            return DB::transaction(function() use ($id) {
                $result = $this->tagRepository->delete($id);

                if (!$result) {
                    throw new DeleteResourceFailedException();
                }

                return $result;
            });
        } catch (\Exception $e) {
            throw new DeleteResourceFailedException();
        }
    }
}
