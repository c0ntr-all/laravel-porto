<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Folder\Tests\Functional;

use App\Containers\AppSection\User\Models\User;
use App\Containers\MovieSection\Folder\Enums\SystemMovieFolderEnum;
use App\Containers\MovieSection\Folder\Models\Folder;
use App\Containers\MovieSection\Folder\Support\FolderMoviesCountCache;
use App\Containers\MovieSection\Movie\Models\Movie;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class FolderCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_unauthenticated_user_cannot_list_folders(): void
    {
        $this->getJson('/api/v1/movie/folders')->assertUnauthorized();
    }

    public function test_new_user_receives_three_system_folders(): void
    {
        $response = $this->actingAs($this->user, 'api')
            ->getJson('/api/v1/movie/folders');

        $response->assertOk();
        $this->assertCount(3, $response->json('data'));

        $names = collect($response->json('data'))->pluck('attributes.name')->all();
        $this->assertEqualsCanonicalizing([
            'Буду смотреть',
            'Просмотрено',
            'Любимые фильмы',
        ], $names);

        foreach (SystemMovieFolderEnum::cases() as $folder) {
            $this->assertDatabaseHas('movie_folders', [
                'user_id' => $this->user->id,
                'slug' => $folder->value,
                'name' => $folder->folderName(),
                'is_system' => 1,
            ]);
        }
    }

    public function test_user_can_create_update_get_and_delete_custom_folder(): void
    {
        $created = $this->actingAs($this->user, 'api')
            ->postJson('/api/v1/movie/folders', [
                'name' => 'Ночной просмотр',
            ]);

        $created->assertCreated()
            ->assertJsonPath('data.type', 'movie_folders')
            ->assertJsonPath('data.attributes.name', 'Ночной просмотр')
            ->assertJsonPath('data.attributes.user_id', $this->user->id)
            ->assertJsonPath('data.attributes.is_system', false)
            ->assertJsonPath('data.attributes.movies_count', 0);

        $folderId = (int) $created->json('data.id');

        $this->actingAs($this->user, 'api')
            ->getJson('/api/v1/movie/folders/'.$folderId)
            ->assertOk()
            ->assertJsonPath('data.attributes.name', 'Ночной просмотр');

        $this->actingAs($this->user, 'api')
            ->patchJson('/api/v1/movie/folders/'.$folderId, [
                'name' => 'Классика',
            ])
            ->assertOk()
            ->assertJsonPath('data.attributes.name', 'Классика');

        $this->actingAs($this->user, 'api')
            ->deleteJson('/api/v1/movie/folders/'.$folderId)
            ->assertOk();

        $this->assertDatabaseMissing('movie_folders', ['id' => $folderId]);
    }

    public function test_movie_list_includes_current_user_folder_slugs(): void
    {
        $watchlist = Folder::query()
            ->where('user_id', $this->user->id)
            ->where('slug', SystemMovieFolderEnum::WATCHLIST->value)
            ->firstOrFail();
        $movie = Movie::factory()->create(['title' => 'Folder Marker']);

        $this->actingAs($this->user, 'api')
            ->postJson('/api/v1/movie/folders/'.$watchlist->id.'/movies', [
                'movie_id' => $movie->id,
            ])
            ->assertOk();

        $list = $this->actingAs($this->user, 'api')
            ->getJson('/api/v1/movie/movies?filter[title]=Folder Marker');

        $list->assertOk();
        $this->assertSame(['watchlist'], $list->json('data.0.attributes.folder_slugs'));
    }

    public function test_system_folders_cannot_be_renamed_or_deleted(): void
    {
        $folder = Folder::query()
            ->where('user_id', $this->user->id)
            ->where('slug', SystemMovieFolderEnum::WATCHLIST->value)
            ->firstOrFail();

        $this->actingAs($this->user, 'api')
            ->patchJson('/api/v1/movie/folders/'.$folder->id, [
                'name' => 'Другое имя',
            ])
            ->assertStatus(422);

        $this->actingAs($this->user, 'api')
            ->deleteJson('/api/v1/movie/folders/'.$folder->id)
            ->assertStatus(422);

        $this->assertDatabaseHas('movie_folders', [
            'id' => $folder->id,
            'name' => 'Буду смотреть',
        ]);
    }

    public function test_folders_are_isolated_per_user(): void
    {
        $other = User::factory()->create();
        $folder = Folder::factory()->create([
            'user_id' => $other->id,
            'name' => 'Чужая папка',
        ]);

        $this->actingAs($this->user, 'api')
            ->getJson('/api/v1/movie/folders/'.$folder->id)
            ->assertNotFound();

        $list = $this->actingAs($this->user, 'api')
            ->getJson('/api/v1/movie/folders');
        $list->assertOk();
        $names = collect($list->json('data'))->pluck('attributes.name')->all();
        $this->assertNotContains('Чужая папка', $names);
    }

    public function test_user_can_attach_list_sort_paginate_and_detach_movies(): void
    {
        $folder = Folder::query()
            ->where('user_id', $this->user->id)
            ->where('slug', SystemMovieFolderEnum::FAVORITES->value)
            ->firstOrFail();

        $alpha = Movie::factory()->create([
            'title' => 'Alpha',
            'year' => 2010,
            'kp_rating' => 5.0,
        ]);
        $bravo = Movie::factory()->create([
            'title' => 'Bravo',
            'year' => 2000,
            'kp_rating' => 9.0,
        ]);
        $charlie = Movie::factory()->create([
            'title' => 'Charlie',
            'year' => 1990,
            'kp_rating' => 7.0,
        ]);

        $this->actingAs($this->user, 'api')
            ->postJson('/api/v1/movie/folders/'.$folder->id.'/movies', [
                'movie_id' => $bravo->id,
            ])
            ->assertOk()
            ->assertJsonPath('data.attributes.movies_count', 1);

        $this->travel(2)->seconds();
        $this->actingAs($this->user, 'api')
            ->postJson('/api/v1/movie/folders/'.$folder->id.'/movies', [
                'movie_id' => $alpha->id,
            ])
            ->assertOk()
            ->assertJsonPath('data.attributes.movies_count', 2);

        $this->travel(2)->seconds();
        $this->actingAs($this->user, 'api')
            ->postJson('/api/v1/movie/folders/'.$folder->id.'/movies', [
                'movie_id' => $charlie->id,
            ])
            ->assertOk()
            ->assertJsonPath('data.attributes.movies_count', 3);

        $this->actingAs($this->user, 'api')
            ->postJson('/api/v1/movie/folders/'.$folder->id.'/movies', [
                'movie_id' => $charlie->id,
            ])
            ->assertOk()
            ->assertJsonPath('data.attributes.movies_count', 3);

        $byTitle = $this->actingAs($this->user, 'api')
            ->getJson('/api/v1/movie/folders/'.$folder->id.'/movies?sort=title');
        $byTitle->assertOk();
        $this->assertSame(['Alpha', 'Bravo', 'Charlie'], collect($byTitle->json('data'))->pluck('attributes.title')->all());
        $this->assertNotNull($byTitle->json('data.0.attributes.added_at'));

        $byYear = $this->actingAs($this->user, 'api')
            ->getJson('/api/v1/movie/folders/'.$folder->id.'/movies?sort=-year');
        $this->assertSame(['Alpha', 'Bravo', 'Charlie'], collect($byYear->json('data'))->pluck('attributes.title')->all());

        $byRating = $this->actingAs($this->user, 'api')
            ->getJson('/api/v1/movie/folders/'.$folder->id.'/movies?sort=-kp_rating');
        $this->assertSame(['Bravo', 'Charlie', 'Alpha'], collect($byRating->json('data'))->pluck('attributes.title')->all());

        $byAdded = $this->actingAs($this->user, 'api')
            ->getJson('/api/v1/movie/folders/'.$folder->id.'/movies?sort=-added_at');
        $this->assertSame(['Charlie', 'Alpha', 'Bravo'], collect($byAdded->json('data'))->pluck('attributes.title')->all());

        $page = $this->actingAs($this->user, 'api')
            ->getJson('/api/v1/movie/folders/'.$folder->id.'/movies?sort=title&per_page=1&page=2');
        $page->assertOk()
            ->assertJsonPath('meta.current_page', 2)
            ->assertJsonPath('meta.per_page', 1)
            ->assertJsonPath('meta.total', 3)
            ->assertJsonPath('meta.last_page', 3);
        $this->assertCount(1, $page->json('data'));
        $this->assertSame('Bravo', $page->json('data.0.attributes.title'));

        $this->assertTrue(Cache::has(FolderMoviesCountCache::key($folder->id)));

        $this->actingAs($this->user, 'api')
            ->deleteJson('/api/v1/movie/folders/'.$folder->id.'/movies/'.$alpha->id)
            ->assertOk()
            ->assertJsonPath('data.attributes.movies_count', 2);
    }
}
