<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Post\Tests\Functional;

use App\Containers\AppSection\User\Models\User;
use App\Containers\LifelogSection\Post\Models\Post;
use App\Containers\AppSection\Tag\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdatePostTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_update_post_with_tags(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $post = Post::factory()->create([
            'user_id' => $user->id,
            'title' => 'Old title',
        ]);

        $tags = Tag::factory()->count(2)->create(['user_id' => $user->id]);

        $response = $this->putJson("/api/posts/{$post->id}", [
            'title' => 'Updated title',
            'content' => 'Updated content',
            'tags' => $tags->pluck('id')->toArray(),
        ]);

        $response->assertOk()
                 ->assertJsonStructure([
                     'data' => [
                         'id',
                         'title',
                         'content',
                         'tags' => [
                             ['id', 'name'],
                         ],
                     ],
                 ]);

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'title' => 'Updated title',
        ]);

        foreach ($tags as $tag) {
            $this->assertDatabaseHas('post_tag', [
                'post_id' => $post->id,
                'tag_id' => $tag->id,
            ]);
        }
    }
}
