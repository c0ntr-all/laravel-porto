<?php declare(strict_types=1);

namespace App\Containers\AppSection\Tag\Tasks;

use App\Containers\AppSection\Tag\Data\DTO\TagCreateDto;
use App\Containers\AppSection\Tag\Data\Repositories\TagRepository;
use App\Ship\Parents\Tasks\Task;
use Illuminate\Support\Str;

class CreateTagTask extends Task
{
    public function __construct(
        private readonly TagRepository $tagRepository
    ) {
    }

    public function run(TagCreateDto $dto)
    {
        $dto->slug = Str::slug($dto->name);

        return $this->tagRepository->create([
            'user_id' => $dto->user_id,
            'name' => $dto->name,
            'slug' => $dto->slug,
            'description' => $dto->description ?? $dto->content,
            'icon' => $dto->icon,
            'color' => $dto->color,
            'parent_id' => $dto->parent_id,
        ]);
    }
}
