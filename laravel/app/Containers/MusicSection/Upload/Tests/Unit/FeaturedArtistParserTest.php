<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Upload\Tests\Unit;

use App\Containers\MusicSection\Upload\Support\FeaturedArtistParser;
use Tests\TestCase;

class FeaturedArtistParserTest extends TestCase
{
    public function test_it_extracts_multiple_featured_names_and_ignores_producers(): void
    {
        $parser = app(FeaturedArtistParser::class);

        $this->assertSame(
            ['Kendrick Lamar', 'Jay Rock', 'Ab-Soul'],
            $parser->namesFromCredits('feat. Kendrick Lamar, Jay Rock & Ab-Soul / prod. Mike Will'),
        );
        $this->assertSame(['Rival'], $parser->namesFromCredits('vs. Rival'));
        $this->assertSame(['Guest'], $parser->namesFromCredits('ft. Guest'));
        $this->assertSame([], $parser->namesFromCredits('prod. Dr. Dre'));
        $this->assertSame(['Tyler, The Creator'], $parser->namesFromCredits('feat. Tyler, The Creator'));
    }

    public function test_it_strips_feat_from_the_artist_tag(): void
    {
        $parser = app(FeaturedArtistParser::class);

        $this->assertSame(
            ['name' => 'Drake', 'featured_artists' => ['Rihanna']],
            $parser->parseArtistField('Drake feat. Rihanna'),
        );
        $this->assertSame(
            ['name' => 'Metallica', 'featured_artists' => []],
            $parser->parseArtistField('Metallica'),
        );
    }
}
