<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Artist\Tests\Functional;

use App\Containers\AppSection\User\Models\User;
use App\Containers\MusicSection\Artist\Models\Artist;
use App\Containers\MusicSection\Tag\Models\MusicTag;
use App\Containers\MusicSection\Tag\Models\MusicTagGroup;
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

    public function test_filter_tags_or_matches_any_selected_tag(): void
    {
        $user = User::factory()->create();
        [$metal, $dark] = $this->makeTags();
        $metalArtist = $this->makeArtist($user, 'Metallica');
        $darkArtist = $this->makeArtist($user, 'Type O Negative');
        $this->makeArtist($user, 'Untagged');
        $this->attachTag($metalArtist, $metal);
        $this->attachTag($darkArtist, $dark);

        $response = $this->actingAs($user, 'api')
            ->getJson('/api/v1/music/artists?'.http_build_query([
                'filter' => [
                    'tags' => $metal->id.','.$dark->id,
                    'tags_match' => 'or',
                ],
                'per_page' => 10,
            ]));

        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id')->map(fn ($id) => (int) $id)->all();
        $this->assertEqualsCanonicalizing([$metalArtist->id, $darkArtist->id], $ids);
    }

    public function test_filter_tags_and_requires_every_selected_tag(): void
    {
        $user = User::factory()->create();
        [$metal, $dark] = $this->makeTags();
        $both = $this->makeArtist($user, 'Both');
        $metalOnly = $this->makeArtist($user, 'Metal Only');
        $this->attachTag($both, $metal);
        $this->attachTag($both, $dark);
        $this->attachTag($metalOnly, $metal);

        $response = $this->actingAs($user, 'api')
            ->getJson('/api/v1/music/artists?'.http_build_query([
                'filter' => [
                    'tags' => $metal->id.','.$dark->id,
                    'tags_match' => 'and',
                ],
                'per_page' => 10,
            ]));

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
        $this->assertSame($both->id, (int) $response->json('data.0.id'));
    }

    public function test_filter_tags_nested_includes_descendants(): void
    {
        $user = User::factory()->create();
        [$metal, $doom] = $this->makeNestedTags();
        $artist = $this->makeArtist($user, 'Warning');
        $this->attachTag($artist, $doom);

        $nested = $this->actingAs($user, 'api')
            ->getJson('/api/v1/music/artists?'.http_build_query([
                'filter' => [
                    'tags' => (string) $metal->id,
                    'tags_nested' => '1',
                ],
                'per_page' => 10,
            ]));

        $nested->assertOk();
        $this->assertCount(1, $nested->json('data'));
        $this->assertSame($artist->id, (int) $nested->json('data.0.id'));

        $strict = $this->actingAs($user, 'api')
            ->getJson('/api/v1/music/artists?'.http_build_query([
                'filter' => [
                    'tags' => (string) $metal->id,
                    'tags_nested' => '0',
                ],
                'per_page' => 10,
            ]));

        $strict->assertOk();
        $this->assertCount(0, $strict->json('data'));
    }

    private function makeArtist(User $user, string $name): Artist
    {
        return Artist::query()->create([
            'user_id' => $user->id,
            'name' => $name,
            'path' => 'F:\\Music\\'.$name,
        ]);
    }

    /**
     * @return array{0: MusicTag, 1: MusicTag}
     */
    private function makeTags(): array
    {
        $group = MusicTagGroup::query()->create([
            'name' => 'Genre',
            'slug' => 'genre-'.uniqid(),
            'is_system' => true,
        ]);
        $metal = MusicTag::query()->create([
            'name' => 'Metal',
            'group_id' => $group->id,
            'is_active' => true,
        ]);
        $dark = MusicTag::query()->create([
            'name' => 'Dark',
            'group_id' => $group->id,
            'is_active' => true,
        ]);

        return [$metal, $dark];
    }

    /**
     * @return array{0: MusicTag, 1: MusicTag}
     */
    private function makeNestedTags(): array
    {
        $group = MusicTagGroup::query()->create([
            'name' => 'Genre',
            'slug' => 'genre-nested-'.uniqid(),
            'is_system' => true,
        ]);
        $metal = MusicTag::query()->create([
            'name' => 'Metal',
            'group_id' => $group->id,
            'is_active' => true,
        ]);
        $doom = MusicTag::query()->create([
            'name' => 'Doom',
            'group_id' => $group->id,
            'parent_id' => $metal->id,
            'is_active' => true,
        ]);

        return [$metal, $doom];
    }

    private function attachTag(Artist $artist, MusicTag $tag): void
    {
        $artist->tags()->attach($tag->id, [
            'tracks_count' => 1,
            'percentage' => 100,
        ]);
    }
}
