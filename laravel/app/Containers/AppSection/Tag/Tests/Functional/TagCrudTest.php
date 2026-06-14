<?php declare(strict_types=1);

namespace App\Containers\AppSection\Tag\Tests\Functional;

use App\Containers\AppSection\User\Models\User;
use App\Containers\AppSection\Tag\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_user_can_create_tag(): void
    {
        $response = $this->actingAs($this->user, 'api')
            ->postJson('/api/v1/tags', [
                'name' => 'New Tag',
                'content' => 'Tag description',
            ]);

        $response->assertOk()
            ->assertJsonStructure([
                'data' => ['id', 'attributes' => ['name', 'slug']],
                'meta' => ['message'],
            ]);

        $this->assertDatabaseHas('tags', [
            'name' => 'New Tag',
            'slug' => 'new-tag',
        ]);
    }

    public function test_user_cannot_create_duplicate_tag(): void
    {
        Tag::create([
            'user_id' => $this->user->id,
            'name' => 'Existing Tag',
            'slug' => 'existing-tag',
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->postJson('/api/v1/tags', [
                'name' => 'Existing Tag',
            ]);

        $response->assertUnprocessable();
    }

    public function test_user_can_list_tags(): void
    {
        Tag::create([
            'user_id' => $this->user->id,
            'name' => 'Tag One',
            'slug' => 'tag-one',
        ]);
        Tag::create([
            'user_id' => $this->user->id,
            'name' => 'Tag Two',
            'slug' => 'tag-two',
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->getJson('/api/v1/tags');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => ['*' => ['id', 'type', 'attributes']],
            ]);
    }

    public function test_user_can_get_tag_by_slug(): void
    {
        Tag::create([
            'user_id' => $this->user->id,
            'name' => 'Findable Tag',
            'slug' => 'findable-tag',
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->getJson('/api/v1/tags/findable-tag');

        $response->assertOk();
    }

    public function test_user_can_delete_tag(): void
    {
        $tag = Tag::create([
            'user_id' => $this->user->id,
            'name' => 'Delete Me',
            'slug' => 'delete-me',
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->deleteJson("/api/v1/tags/{$tag->id}");

        $response->assertOk();

        $this->assertDatabaseMissing('tags', ['id' => $tag->id]);
    }

    public function test_unauthenticated_user_cannot_create_tag(): void
    {
        $response = $this->postJson('/api/v1/tags', [
            'name' => 'Unauthorized Tag',
        ]);

        $response->assertUnauthorized();
    }
}
