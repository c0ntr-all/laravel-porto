<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Upload\Tests\Unit;

use App\Containers\MusicSection\Upload\Tasks\ParseAlbumTitleTask;
use Tests\TestCase;

class ParseAlbumTitleTaskTest extends TestCase
{
    public function test_it_treats_custom_parentheticals_as_edition_and_strips_them_from_the_name(): void
    {
        $parsed = $this->parse('Master Of Puppets (Limited Digipack Edition)');

        $this->assertSame('Master Of Puppets', $parsed['name']);
        $this->assertSame('Limited Digipack Edition', $parsed['edition']);
        $this->assertSame('Master Of Puppets', $parsed['original_album']);
        $this->assertSame(1, $parsed['album_type_id']);
    }

    public function test_it_maps_known_type_slugs_instead_of_edition(): void
    {
        $parsed = $this->parse('Killers (Live)');

        $this->assertSame('Killers', $parsed['name']);
        $this->assertNull($parsed['edition']);
        $this->assertNull($parsed['original_album']);
        $this->assertSame(6, $parsed['album_type_id']);
    }

    public function test_it_keeps_feat_and_disc_markers_in_the_title(): void
    {
        $parsed = $this->parse('Collab (feat. Guest) (CD1)');

        $this->assertSame('Collab (feat. Guest) (CD1)', $parsed['name']);
        $this->assertNull($parsed['edition']);
        $this->assertNull($parsed['original_album']);
    }

    public function test_it_keeps_a_bare_year_in_the_title(): void
    {
        $parsed = $this->parse('Killers (1981)');

        $this->assertSame('Killers (1981)', $parsed['name']);
        $this->assertNull($parsed['edition']);
    }

    public function test_it_joins_multiple_editions_and_still_detects_type(): void
    {
        $parsed = $this->parse('Killers (Live) (2016 Remaster) (Deluxe)');

        $this->assertSame('Killers', $parsed['name']);
        $this->assertSame('2016 Remaster / Deluxe', $parsed['edition']);
        $this->assertSame('Killers', $parsed['original_album']);
        $this->assertSame(6, $parsed['album_type_id']);
    }

    public function test_it_matches_type_names_with_spaces(): void
    {
        $parsed = $this->parse('Single Track (Maxi Single)');

        $this->assertSame('Single Track', $parsed['name']);
        $this->assertNull($parsed['edition']);
        $this->assertSame(4, $parsed['album_type_id']);
    }

    /**
     * @return array{name: string, edition: string|null, original_album: string|null, album_type_id: int}
     */
    private function parse(string $title): array
    {
        return app(ParseAlbumTitleTask::class)->run($title, [
            (object) ['id' => 1, 'slug' => 'studio', 'name' => 'studio'],
            (object) ['id' => 2, 'slug' => 'ep', 'name' => 'ep'],
            (object) ['id' => 3, 'slug' => 'single', 'name' => 'single'],
            (object) ['id' => 4, 'slug' => 'maxi-single', 'name' => 'maxi-single'],
            (object) ['id' => 5, 'slug' => 'split', 'name' => 'split'],
            (object) ['id' => 6, 'slug' => 'live', 'name' => 'live'],
        ]);
    }
}
