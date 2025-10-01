<?php declare(strict_types=1);

namespace App\Containers\AppSection\Tag\Tasks;

use App\Containers\AppSection\Tag\Data\DTO\TagCreateDto;
use App\Containers\AppSection\Tag\Data\DTO\TagsCreateDto;
use App\Ship\Parents\Tasks\Task;
use Illuminate\Support\Collection;

class CreateTagsByNamesTask extends Task
{
    public function __construct(
        private readonly CreateTagTask $createTagTask
    ) {
    }

    public function run(TagsCreateDto $dto): Collection
    {
        return collect($dto->names)->map(function (string $name) use ($dto) {
            $tagCreateDto = TagCreateDto::from([
                'user_id' => $dto->user_id,
                'name' => $name
            ]);

            return $this->createTagTask->run($tagCreateDto);
        });
    }
}
