<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Artist\Tests\Functional;

use App\Containers\AppSection\User\Models\User;
use App\Containers\MusicSection\Artist\Models\Artist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListArtistsTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_cannot_list_artists(): void
    {
        $this->getJson('/api/v1/music/artists')->assertUnauthorized();
    }

    public function test_first_page_includes_cursor_meta_for_infinite_scroll(): void
    {
        $user = User::factory()->create();
        $this->makeArtist($user, 'A');
        $this->makeArtist($user, 'B');
        $this->makeArtist($user, 'C');

        $response = $this->actingAs($user, 'api')
            ->getJson('/api/v1/music/artists?per_page=2');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'attributes' => ['name', 'description', 'image', 'created_at']],
                ],
                'meta' => [
                    'cursor' => ['current', 'prev', 'next', 'count'],
                    'per_page',
                    'has_more',
                    'next_cursor',
                    'prev_cursor',
                    'next_page_url',
                    'prev_page_url',
                ],
            ]);

        $this->assertCount(2, $response->json('data'));
        $this->assertTrue($response->json('meta.has_more'));
        $this->assertSame(2, $response->json('meta.per_page'));
        $this->assertSame(2, $response->json('meta.cursor.count'));
        $this->assertNotEmpty($response->json('meta.next_cursor'));
        $this->assertNotEmpty($response->json('meta.next_page_url'));
        $this->assertSame(
            $response->json('meta.next_cursor'),
            $response->json('meta.cursor.next'),
        );
    }

    public function test_cursor_loads_the_next_slice_without_duplicates(): void
    {
        $user = User::factory()->create();
        $this->makeArtist($user, 'First');
        $this->makeArtist($user, 'Second');
        $this->makeArtist($user, 'Third');

        $firstPage = $this->actingAs($user, 'api')
            ->getJson('/api/v1/music/artists?per_page=2')
            ->assertOk();

        $cursor = $firstPage->json('meta.next_cursor');
        $this->assertNotEmpty($cursor);

        $secondPage = $this->actingAs($user, 'api')
            ->getJson('/api/v1/music/artists?per_page=2&cursor='.urlencode($cursor))
            ->assertOk();

        $firstIds = collect($firstPage->json('data'))->pluck('id')->all();
        $secondIds = collect($secondPage->json('data'))->pluck('id')->all();

        $this->assertCount(1, $secondIds);
        $this->assertEmpty(array_intersect($firstIds, $secondIds));
        $this->assertFalse($secondPage->json('meta.has_more'));
        $this->assertNull($secondPage->json('meta.next_cursor'));
    }

    public function test_per_page_above_the_limit_is_rejected(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'api')
            ->getJson('/api/v1/music/artists?per_page=101')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['per_page']);
    }

    public function test_filter_by_name_works_with_cursor_pagination(): void
    {
        $user = User::factory()->create();
        $metallica = $this->makeArtist($user, 'Metallica');
        $this->makeArtist($user, 'Megadeth');

        $response = $this->actingAs($user, 'api')
            ->getJson('/api/v1/music/artists?filter[name]=Metal&per_page=10');

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
        $this->assertSame($metallica->id, (int) $response->json('data.0.id'));
        $this->assertFalse($response->json('meta.has_more'));
    }

    private function makeArtist(User $user, string $name): Artist
    {
        return Artist::query()->create([
            'user_id' => $user->id,
            'name' => $name,
            'path' => 'F:\\Music\\'.$name,
        ]);
    }
}
