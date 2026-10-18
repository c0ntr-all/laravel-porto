<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Post\Data\Factories;

use App\Containers\AppSection\Tag\Models\Tag;
use App\Containers\AppSection\User\Models\User;
use App\Containers\LifelogSection\Post\Enums\PostContentTypeEnum;
use App\Containers\LifelogSection\Post\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Collection;

/**
 * @extends Factory<Post>
 */
class PostFactory extends Factory
{
    protected $model = Post::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $datetime = fake()->dateTimeBetween('-2 years', 'now');

        return [
            'user_id' => User::factory(),
            'title' => fake()->unique()->sentence(fake()->numberBetween(3, 8)),
            'content' => fake()->paragraphs(fake()->numberBetween(1, 3), true),
            'content_type' => PostContentTypeEnum::DEFAULT,
            'date' => $datetime->format('Y-m-d'),
            'time' => fake()->boolean(70) ? $datetime->format('H:i:s') : null,
        ];
    }

    public function contentType(PostContentTypeEnum $contentType): static
    {
        return $this->state(fn () => [
            'content_type' => $contentType,
        ]);
    }

    /**
     * @param  Collection<int, Tag>|iterable<Tag>  $tags
     */
    public function withTags(iterable $tags, ?int $count = null): static
    {
        $tagCollection = Collection::make($tags)->values();

        return $this->afterCreating(function (Post $post) use ($tagCollection, $count): void {
            if ($tagCollection->isEmpty()) {
                return;
            }

            $take = $count ?? fake()->numberBetween(1, min(3, $tagCollection->count()));
            $selected = $tagCollection->random($take);
            $selected = $selected instanceof Tag ? collect([$selected]) : collect($selected);

            foreach ($selected as $tag) {
                $post->attachTag($tag, (int) $post->user_id);
            }
        });
    }
}
