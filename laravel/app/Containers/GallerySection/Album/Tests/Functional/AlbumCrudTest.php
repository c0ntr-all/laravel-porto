<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Album\Tests\Functional;

use App\Containers\AppSection\User\Models\User;
use App\Containers\GallerySection\Album\Models\Album;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Enums\EventTypesEnum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AlbumCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_user_can_create_album(): void
    {
        $response = $this->actingAs($this->user, 'api')
            ->postJson('/api/v1/gallery/albums', [
                'name' => 'Vacation',
                'description' => 'Summer 2026',
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.type', 'albums')
            ->assertJsonPath('data.attributes.name', 'Vacation')
            ->assertJsonStructure([
                'data' => ['id', 'type', 'attributes' => ['name', 'description']],
                'meta' => ['message'],
            ]);

        $this->assertDatabaseHas('gallery_albums', [
            'name' => 'Vacation',
            'user_id' => $this->user->id,
        ]);

        $albumId = $response->json('data.id');
        $this->assertDatabaseHas('activity_use_case_logs', [
            'user_id' => $this->user->id,
            'loggable_type' => ContainerAliasEnum::GALLERY_ALBUM->value,
            'loggable_id' => $albumId,
            'event_type' => EventTypesEnum::CREATED->value,
        ]);
    }

    public function test_user_can_list_and_get_album(): void
    {
        $album = Album::create([
            'user_id' => $this->user->id,
            'name' => 'Listed',
        ]);

        $list = $this->actingAs($this->user, 'api')
            ->getJson('/api/v1/gallery/albums');

        $list->assertOk()
            ->assertJsonPath('data.0.type', 'albums');

        $get = $this->actingAs($this->user, 'api')
            ->getJson("/api/v1/gallery/albums/{$album->id}");

        $get->assertOk()
            ->assertJsonPath('data.type', 'albums')
            ->assertJsonPath('data.attributes.name', 'Listed')
            ->assertJsonStructure([
                'included',
            ]);
    }

    public function test_user_can_update_album(): void
    {
        $album = Album::create([
            'user_id' => $this->user->id,
            'name' => 'Old name',
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->patchJson("/api/v1/gallery/albums/{$album->id}", [
                'name' => 'New name',
            ]);

        $response->assertOk()
            ->assertJsonPath('data.attributes.name', 'New name');

        $this->assertDatabaseHas('activity_use_case_logs', [
            'loggable_type' => ContainerAliasEnum::GALLERY_ALBUM->value,
            'loggable_id' => (string) $album->id,
            'event_type' => EventTypesEnum::UPDATED->value,
        ]);
    }

    public function test_user_can_delete_album(): void
    {
        $album = Album::create([
            'user_id' => $this->user->id,
            'name' => 'Disposable',
        ]);

        $this->actingAs($this->user, 'api')
            ->deleteJson("/api/v1/gallery/albums/{$album->id}")
            ->assertOk();

        $this->assertDatabaseMissing('gallery_albums', ['id' => $album->id]);
    }

    public function test_system_album_cannot_be_deleted(): void
    {
        $system = Album::withoutGlobalScopes()->whereNotNull('system_code')->first();
        $this->assertNotNull($system);

        $this->actingAs($this->user, 'api')
            ->deleteJson("/api/v1/gallery/albums/{$system->id}")
            ->assertUnprocessable();
    }

    public function test_unauthenticated_user_cannot_create_album(): void
    {
        $this->postJson('/api/v1/gallery/albums', ['name' => 'Nope'])
            ->assertUnauthorized();
    }
}
