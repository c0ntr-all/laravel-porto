<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Post\Tests\Functional;

use App\Containers\AppSection\User\Models\User;
use App\Containers\LifelogSection\Post\Enums\PostContentTypeEnum;
use App\Containers\LifelogSection\Post\Models\Post;
use App\Ship\Enums\ContainerAliasEnum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostCommentsTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Post $post;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->post = Post::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'Post with comments',
            'content_type' => PostContentTypeEnum::DEFAULT,
            'date' => '2026-11-05',
        ]);
    }

    public function test_user_can_create_comment_on_post(): void
    {
        $response = $this->actingAs($this->user, 'api')
            ->postJson('/api/v1/comments', [
                'commentable_id' => (string) $this->post->id,
                'commentable_type' => ContainerAliasEnum::LL_POST->value,
                'content' => 'First comment',
            ]);

        $response->assertOk()
            ->assertJsonPath('data.type', ContainerAliasEnum::COMMENTS->value)
            ->assertJsonPath('data.attributes.commentable_id', (string) $this->post->id)
            ->assertJsonPath('data.attributes.commentable_type', ContainerAliasEnum::LL_POST->value)
            ->assertJsonPath('data.attributes.content', 'First comment');

        $this->assertDatabaseHas('comments', [
            'commentable_id' => $this->post->id,
            'commentable_type' => ContainerAliasEnum::LL_POST->value,
            'content' => 'First comment',
            'user_id' => $this->user->id,
        ]);
    }

    public function test_user_can_list_comments_for_post(): void
    {
        $this->actingAs($this->user, 'api')
            ->postJson('/api/v1/comments', [
                'commentable_id' => (string) $this->post->id,
                'commentable_type' => ContainerAliasEnum::LL_POST->value,
                'content' => 'Listed comment',
            ])
            ->assertOk();

        $list = $this->actingAs($this->user, 'api')
            ->getJson('/api/v1/comments?' . http_build_query([
                'filter' => [
                    'commentable_id' => $this->post->id,
                    'commentable_type' => ContainerAliasEnum::LL_POST->value,
                ],
            ]));

        $list->assertOk()
            ->assertJsonPath('meta.count', 1)
            ->assertJsonPath('data.0.attributes.content', 'Listed comment');
    }

    public function test_posts_list_includes_comments_count(): void
    {
        $this->actingAs($this->user, 'api')
            ->postJson('/api/v1/comments', [
                'commentable_id' => (string) $this->post->id,
                'commentable_type' => ContainerAliasEnum::LL_POST->value,
                'content' => 'One',
            ])
            ->assertOk();

        $this->actingAs($this->user, 'api')
            ->postJson('/api/v1/comments', [
                'commentable_id' => (string) $this->post->id,
                'commentable_type' => ContainerAliasEnum::LL_POST->value,
                'content' => 'Two',
            ])
            ->assertOk();

        $list = $this->actingAs($this->user, 'api')
            ->getJson('/api/v1/lifelog/posts');

        $list->assertOk()
            ->assertJsonPath('data.0.id', (string) $this->post->id)
            ->assertJsonPath('data.0.attributes.comments_count', 2);
    }

    public function test_posts_list_can_include_comments(): void
    {
        $this->actingAs($this->user, 'api')
            ->postJson('/api/v1/comments', [
                'commentable_id' => (string) $this->post->id,
                'commentable_type' => ContainerAliasEnum::LL_POST->value,
                'content' => 'Included comment',
            ])
            ->assertOk();

        $list = $this->actingAs($this->user, 'api')
            ->getJson('/api/v1/lifelog/posts?include=comments');

        $list->assertOk()
            ->assertJsonPath('data.0.attributes.comments_count', 1)
            ->assertJsonPath('data.0.relationships.comments.meta.count', 1);

        $included = collect($list->json('included') ?? []);
        $this->assertTrue(
            $included->contains(
                fn (array $item): bool => ($item['type'] ?? null) === ContainerAliasEnum::COMMENTS->value
                    && ($item['attributes']['content'] ?? null) === 'Included comment'
            )
        );
    }

    public function test_create_post_response_includes_zero_comments_count(): void
    {
        $response = $this->actingAs($this->user, 'api')
            ->postJson('/api/v1/lifelog/posts', [
                'title' => 'Fresh post',
                'date' => '2026-11-05',
                'tags' => [],
                'new_tags' => [],
            ]);

        $response->assertOk()
            ->assertJsonPath('data.attributes.comments_count', 0);
    }

    public function test_author_can_update_own_comment(): void
    {
        $created = $this->actingAs($this->user, 'api')
            ->postJson('/api/v1/comments', [
                'commentable_id' => (string) $this->post->id,
                'commentable_type' => ContainerAliasEnum::LL_POST->value,
                'content' => 'Original',
            ])
            ->assertOk();

        $commentId = $created->json('data.id');

        $this->actingAs($this->user, 'api')
            ->patchJson('/api/v1/comments/'.$commentId, [
                'content' => '  Edited comment  ',
            ])
            ->assertOk()
            ->assertJsonPath('data.attributes.content', 'Edited comment');

        $this->assertDatabaseHas('comments', [
            'id' => $commentId,
            'content' => 'Edited comment',
            'user_id' => $this->user->id,
        ]);
    }

    public function test_author_can_delete_own_comment(): void
    {
        $created = $this->actingAs($this->user, 'api')
            ->postJson('/api/v1/comments', [
                'commentable_id' => (string) $this->post->id,
                'commentable_type' => ContainerAliasEnum::LL_POST->value,
                'content' => 'To delete',
            ])
            ->assertOk();

        $commentId = $created->json('data.id');

        $this->actingAs($this->user, 'api')
            ->deleteJson('/api/v1/comments/'.$commentId)
            ->assertOk();

        $this->assertSoftDeleted('comments', [
            'id' => $commentId,
        ]);

        $this->actingAs($this->user, 'api')
            ->getJson('/api/v1/lifelog/posts')
            ->assertOk()
            ->assertJsonPath('data.0.attributes.comments_count', 0);
    }

    public function test_other_user_cannot_update_or_delete_comment(): void
    {
        $created = $this->actingAs($this->user, 'api')
            ->postJson('/api/v1/comments', [
                'commentable_id' => (string) $this->post->id,
                'commentable_type' => ContainerAliasEnum::LL_POST->value,
                'content' => 'Owned',
            ])
            ->assertOk();

        $commentId = $created->json('data.id');
        $other = User::factory()->create();

        $this->actingAs($other, 'api')
            ->patchJson('/api/v1/comments/'.$commentId, [
                'content' => 'Hijacked',
            ])
            ->assertForbidden();

        $this->actingAs($other, 'api')
            ->deleteJson('/api/v1/comments/'.$commentId)
            ->assertForbidden();

        $this->assertDatabaseHas('comments', [
            'id' => $commentId,
            'content' => 'Owned',
            'deleted_at' => null,
        ]);
    }
}
