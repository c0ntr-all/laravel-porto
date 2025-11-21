<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Post\Tests\Functional;

use App\Containers\AppSection\User\Models\User;
use App\Containers\LifelogSection\Post\Models\Post;
use App\Containers\AppSection\Tag\Models\Tag;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class UpdatePostTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_update_post_with_attaching_existing_tags(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Post::unsetEventDispatcher();

        $post = $this->createUpdateablePost($user->id);

        $tags = Tag::factory()->count(2)->create(['user_id' => $user->id]);

        $updatedPostData = [
            'title' => 'Updated title',
            'content' => 'Updated content',
            'datetime' => fake()->dateTimeBetween('-2 months', 'now')->format('Y-m-d H:i'),
        ];

        $response = $this->patchJson("/api/v1/lifelog/posts/{$post->id}", [
            ...$updatedPostData,
            'tags' => $tags->pluck('id')->toArray()
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'attributes' => [
                        'title',
                        'content',
                        'datetime',
                    ],
                    'relationships' => [
                        'tags' => [
                            'data' => [
                                ['id', 'type']
                            ],
                        ],
                    ],
                ],
                'included' => [[
                    'type',
                    'id',
                    'attributes'
                ]]
            ]);

        $this->assertDatabaseHas('lifelog_posts', $updatedPostData);

        $tags->each(function (Tag $tag) use ($post): void {
            $this->assertDatabaseHas('tags', [
                'name' => $tag->name,
                'slug' => $tag->slug,
                'content' => $tag->content,
            ]);
            $this->assertDatabaseHas('taggables', [
                'tag_id' => $tag->id,
                'taggable_id' => $post->id,
            ]);
        });
    }

    public function test_user_can_update_post_with_attaching_new_tags(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Post::unsetEventDispatcher();

        $post = $this->createUpdateablePost($user->id);

        $updatedPostData = [
            'title' => 'Updated title',
            'content' => 'Updated content',
            'datetime' => fake()->dateTimeBetween('-2 months', 'now')->format('Y-m-d H:i'),
        ];

        $newTagsNames = [
            fake()->sentence(),
            fake()->sentence(),
            fake()->sentence()
        ];

        $response = $this->patchJson("/api/v1/lifelog/posts/{$post->id}", [
            ...$updatedPostData,
            'new_tags' => $newTagsNames
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'attributes' => [
                        'title',
                        'content',
                        'datetime',
                    ],
                    'relationships' => [
                        'tags' => [
                            'data' => [
                                ['id', 'type']
                            ],
                        ],
                    ],
                ],
                'included' => [[
                    'type',
                    'id',
                    'attributes'
                ]]
            ]);

        $this->assertDatabaseHas('lifelog_posts', $updatedPostData);

        collect($newTagsNames)->each(function (string $tagName) use ($post): void {
            $this->assertDatabaseHas('tags', [
                'name' => $tagName,
                'slug' => Str::slug($tagName)
            ]);
            $this->assertDatabaseHas('taggables', [
                'tag_id' => Tag::where('name', $tagName)->first()->id,
                'taggable_id' => $post->id,
            ]);
        });
    }

    private function createUpdateablePost($userId): Model
    {
        return Post::factory()->create([
            'user_id' => $userId,
            'title' => 'Old title',
            'content' => 'Old content',
            'datetime' => fake()->dateTimeBetween('-2 months', 'now'),
        ]);
    }
}
