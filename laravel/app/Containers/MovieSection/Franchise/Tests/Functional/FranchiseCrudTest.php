<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Franchise\Tests\Functional;

use App\Containers\AppSection\User\Models\User;
use App\Containers\MovieSection\Franchise\Models\Franchise;
use App\Containers\MovieSection\Movie\Models\Movie;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FranchiseCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_unauthenticated_user_cannot_list_franchises(): void
    {
        $this->getJson('/api/v1/movie/franchises')->assertUnauthorized();
    }

    public function test_user_can_create_get_update_and_soft_delete_franchise(): void
    {
        $created = $this->actingAs($this->user, 'api')
            ->postJson('/api/v1/movie/franchises', [
                'name' => 'Marvel Cinematic Universe',
                'description' => 'MCU shared universe',
                'order' => 10,
            ]);

        $created->assertCreated()
            ->assertJsonPath('data.type', 'movie_franchises')
            ->assertJsonPath('data.attributes.name', 'Marvel Cinematic Universe')
            ->assertJsonPath('data.attributes.description', 'MCU shared universe')
            ->assertJsonPath('data.attributes.order', 10)
            ->assertJsonPath('data.attributes.created_by', $this->user->id);

        $franchiseId = (int) $created->json('data.id');

        $this->actingAs($this->user, 'api')
            ->getJson('/api/v1/movie/franchises/'.$franchiseId)
            ->assertOk()
            ->assertJsonPath('data.attributes.name', 'Marvel Cinematic Universe');

        $this->actingAs($this->user, 'api')
            ->patchJson('/api/v1/movie/franchises/'.$franchiseId, [
                'name' => 'MCU',
                'order' => 1,
            ])
            ->assertOk()
            ->assertJsonPath('data.attributes.name', 'MCU')
            ->assertJsonPath('data.attributes.order', 1);

        $this->actingAs($this->user, 'api')
            ->deleteJson('/api/v1/movie/franchises/'.$franchiseId)
            ->assertOk();

        $this->assertSoftDeleted('movie_franchises', ['id' => $franchiseId]);

        $this->actingAs($this->user, 'api')
            ->getJson('/api/v1/movie/franchises/'.$franchiseId)
            ->assertNotFound();
    }

    public function test_franchise_name_must_be_unique_among_active(): void
    {
        Franchise::factory()->create([
            'created_by' => $this->user->id,
            'name' => 'Star Wars',
        ]);

        $this->actingAs($this->user, 'api')
            ->postJson('/api/v1/movie/franchises', [
                'name' => 'Star Wars',
            ])
            ->assertUnprocessable();
    }

    public function test_franchises_are_shared_and_sorted_by_order(): void
    {
        $other = User::factory()->create();

        Franchise::factory()->create([
            'created_by' => $other->id,
            'name' => 'Second',
            'order' => 20,
        ]);
        Franchise::factory()->create([
            'created_by' => $this->user->id,
            'name' => 'First',
            'order' => 5,
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->getJson('/api/v1/movie/franchises');

        $response->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.attributes.name', 'First')
            ->assertJsonPath('data.1.attributes.name', 'Second');
    }

    public function test_user_can_attach_list_reorder_and_detach_movies(): void
    {
        $franchise = Franchise::factory()->create([
            'created_by' => $this->user->id,
            'name' => 'Harry Potter',
            'order' => 1,
        ]);

        $first = Movie::factory()->create(['title' => 'Philosopher Stone', 'year' => 2001]);
        $second = Movie::factory()->create(['title' => 'Chamber of Secrets', 'year' => 2002]);
        $third = Movie::factory()->create(['title' => 'Prisoner of Azkaban', 'year' => 2004]);

        $this->actingAs($this->user, 'api')
            ->postJson('/api/v1/movie/franchises/'.$franchise->id.'/movies', [
                'movie_id' => $second->id,
                'order' => 2,
            ])
            ->assertOk();

        $this->actingAs($this->user, 'api')
            ->postJson('/api/v1/movie/franchises/'.$franchise->id.'/movies', [
                'movie_id' => $first->id,
                'order' => 1,
            ])
            ->assertOk();

        $this->actingAs($this->user, 'api')
            ->postJson('/api/v1/movie/franchises/'.$franchise->id.'/movies', [
                'movie_id' => $third->id,
            ])
            ->assertOk();

        $list = $this->actingAs($this->user, 'api')
            ->getJson('/api/v1/movie/franchises/'.$franchise->id.'/movies');

        $list->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonPath('data.0.attributes.title', 'Philosopher Stone')
            ->assertJsonPath('data.0.attributes.order', 1)
            ->assertJsonPath('data.1.attributes.title', 'Chamber of Secrets')
            ->assertJsonPath('data.1.attributes.order', 2)
            ->assertJsonPath('data.2.attributes.title', 'Prisoner of Azkaban')
            ->assertJsonPath('data.2.attributes.order', 3);

        $this->actingAs($this->user, 'api')
            ->postJson('/api/v1/movie/franchises/'.$franchise->id.'/movies/reorder', [
                'movie_ids' => [$third->id, $first->id, $second->id],
            ])
            ->assertOk();

        $reordered = $this->actingAs($this->user, 'api')
            ->getJson('/api/v1/movie/franchises/'.$franchise->id.'/movies');

        $reordered->assertOk()
            ->assertJsonPath('data.0.attributes.title', 'Prisoner of Azkaban')
            ->assertJsonPath('data.0.attributes.order', 1)
            ->assertJsonPath('data.1.attributes.title', 'Philosopher Stone')
            ->assertJsonPath('data.1.attributes.order', 2)
            ->assertJsonPath('data.2.attributes.title', 'Chamber of Secrets')
            ->assertJsonPath('data.2.attributes.order', 3);

        $this->actingAs($this->user, 'api')
            ->deleteJson('/api/v1/movie/franchises/'.$franchise->id.'/movies/'.$second->id)
            ->assertOk();

        $this->assertDatabaseMissing('movie_franchise_movie', [
            'franchise_id' => $franchise->id,
            'movie_id' => $second->id,
        ]);

        $this->actingAs($this->user, 'api')
            ->getJson('/api/v1/movie/franchises/'.$franchise->id.'/movies')
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_reorder_movies_rejects_incomplete_set(): void
    {
        $franchise = Franchise::factory()->create([
            'created_by' => $this->user->id,
            'name' => 'Incomplete Reorder',
        ]);

        $first = Movie::factory()->create();
        $second = Movie::factory()->create();

        $franchise->movies()->attach($first->id, ['order' => 1]);
        $franchise->movies()->attach($second->id, ['order' => 2]);

        $this->actingAs($this->user, 'api')
            ->postJson('/api/v1/movie/franchises/'.$franchise->id.'/movies/reorder', [
                'movie_ids' => [$second->id],
            ])
            ->assertUnprocessable();
    }

    public function test_user_can_sort_franchise_movies_by_year_and_order(): void
    {
        $franchise = Franchise::factory()->create([
            'created_by' => $this->user->id,
            'name' => 'Sortable',
        ]);

        $early = Movie::factory()->create(['title' => 'Early', 'year' => 1999]);
        $mid = Movie::factory()->create(['title' => 'Mid', 'year' => 2005]);
        $late = Movie::factory()->create(['title' => 'Late', 'year' => 2010]);

        $franchise->movies()->attach($mid->id, ['order' => 1]);
        $franchise->movies()->attach($late->id, ['order' => 2]);
        $franchise->movies()->attach($early->id, ['order' => 3]);

        $byYearAsc = $this->actingAs($this->user, 'api')
            ->getJson('/api/v1/movie/franchises/'.$franchise->id.'/movies?sort=year');

        $byYearAsc->assertOk()
            ->assertJsonPath('data.0.attributes.title', 'Early')
            ->assertJsonPath('data.1.attributes.title', 'Mid')
            ->assertJsonPath('data.2.attributes.title', 'Late');

        $byYearDesc = $this->actingAs($this->user, 'api')
            ->getJson('/api/v1/movie/franchises/'.$franchise->id.'/movies?sort=-year');

        $byYearDesc->assertOk()
            ->assertJsonPath('data.0.attributes.title', 'Late')
            ->assertJsonPath('data.1.attributes.title', 'Mid')
            ->assertJsonPath('data.2.attributes.title', 'Early');

        $byOrderAsc = $this->actingAs($this->user, 'api')
            ->getJson('/api/v1/movie/franchises/'.$franchise->id.'/movies?sort=order');

        $byOrderAsc->assertOk()
            ->assertJsonPath('data.0.attributes.title', 'Mid')
            ->assertJsonPath('data.0.attributes.order', 1)
            ->assertJsonPath('data.1.attributes.title', 'Late')
            ->assertJsonPath('data.1.attributes.order', 2)
            ->assertJsonPath('data.2.attributes.title', 'Early')
            ->assertJsonPath('data.2.attributes.order', 3);

        $byOrderDesc = $this->actingAs($this->user, 'api')
            ->getJson('/api/v1/movie/franchises/'.$franchise->id.'/movies?sort=-order');

        $byOrderDesc->assertOk()
            ->assertJsonPath('data.0.attributes.title', 'Early')
            ->assertJsonPath('data.0.attributes.order', 3)
            ->assertJsonPath('data.1.attributes.title', 'Late')
            ->assertJsonPath('data.1.attributes.order', 2)
            ->assertJsonPath('data.2.attributes.title', 'Mid')
            ->assertJsonPath('data.2.attributes.order', 1);
    }

    public function test_create_without_order_appends_to_end(): void
    {
        Franchise::factory()->create([
            'created_by' => $this->user->id,
            'name' => 'Existing',
            'order' => 7,
        ]);

        $this->actingAs($this->user, 'api')
            ->postJson('/api/v1/movie/franchises', [
                'name' => 'Appended',
            ])
            ->assertCreated()
            ->assertJsonPath('data.attributes.order', 8);
    }

    public function test_user_can_upload_franchise_image_on_create_and_update(): void
    {
        Storage::fake('public');

        $created = $this->actingAs($this->user, 'api')
            ->post('/api/v1/movie/franchises', [
                'name' => 'With Cover',
                'image_file' => UploadedFile::fake()->image('franchise.jpg', 200, 300),
            ], [
                'Accept' => 'application/json',
            ]);

        $created->assertCreated();

        $franchiseId = (int) $created->json('data.id');
        $imagePath = Franchise::query()->findOrFail($franchiseId)->image;

        $this->assertNotNull($imagePath);
        $this->assertStringStartsWith("movies/franchises/{$franchiseId}/images/", $imagePath);
        Storage::disk('public')->assertExists($imagePath);
        $this->assertStringContainsString('/storage/'.$imagePath, (string) $created->json('data.attributes.image'));

        $updated = $this->actingAs($this->user, 'api')
            ->post('/api/v1/movie/franchises/'.$franchiseId, [
                '_method' => 'PATCH',
                'image_file' => UploadedFile::fake()->image('franchise-new.png', 100, 100),
            ], [
                'Accept' => 'application/json',
            ]);

        $updated->assertOk();

        $newImagePath = Franchise::query()->findOrFail($franchiseId)->image;

        $this->assertNotNull($newImagePath);
        $this->assertNotSame($imagePath, $newImagePath);
        Storage::disk('public')->assertExists($newImagePath);
        $this->assertStringContainsString('/storage/'.$newImagePath, (string) $updated->json('data.attributes.image'));
    }
}
