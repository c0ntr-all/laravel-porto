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

    public function run(TagsCreateDto $tagsCreateDto): ?Collection
    {
        if (!empty($tagsCreateDto->new_tags)) {
            return collect($tagsCreateDto->new_tags)->map(function (string $name) use ($tagsCreateDto) {
                $tagCreateDto = TagCreateDto::from([
                    'user_id' => $tagsCreateDto->user_id,
                    'name' => $name
                ]);

                // TODO: cross-section dependency
                return $this->createTagTask->run($tagCreateDto);
            });
        }

        return null;
    }
}
