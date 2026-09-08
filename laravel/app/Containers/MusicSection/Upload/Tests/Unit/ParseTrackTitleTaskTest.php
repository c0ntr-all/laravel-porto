<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Upload\Tests\Unit;

use App\Containers\MusicSection\Upload\Support\TrackTitleCreditsParser;
use App\Containers\MusicSection\Upload\Tasks\ParseTrackTitleTask;
use Tests\TestCase;

class ParseTrackTitleTaskTest extends TestCase
{
    public function test_it_extracts_feat_and_prod_credits_from_brackets(): void
    {
        $parsed = app(ParseTrackTitleTask::class)->run('Humble (feat. Kendrick) [prod. Mike Will]');

        $this->assertSame('Humble', $parsed['name']);
        $this->assertSame('feat. Kendrick / prod. Mike Will', $parsed['credits']);
        $this->assertSame([], $parsed['featured_artists']);
    }

    public function test_it_keeps_artistic_parentheticals_in_the_title(): void
    {
        $parser = app(TrackTitleCreditsParser::class);

        $this->assertSame(
            ['name' => 'Helplessness Blues (Acoustic)', 'credits' => null, 'featured_artists' => []],
            $parser->parse('Helplessness Blues (Acoustic)'),
        );
        $this->assertSame(
            ['name' => 'Bohemian Rhapsody (Live)', 'credits' => null, 'featured_artists' => []],
            $parser->parse('Bohemian Rhapsody (Live)'),
        );
        $this->assertSame(
            ['name' => 'Happiness Is A Warm Gun (It Happened One Night)', 'credits' => null, 'featured_artists' => []],
            $parser->parse('Happiness Is A Warm Gun (It Happened One Night)'),
        );
        $this->assertSame(
            ['name' => 'Song (Club Remix)', 'credits' => null, 'featured_artists' => []],
            $parser->parse('Song (Club Remix)'),
        );
        $this->assertFalse($parser->isCredit('Radio Edit'));
        $this->assertFalse($parser->isCredit('Part II'));
        $this->assertTrue($parser->isCredit('feat. Guest'));
        $this->assertTrue($parser->isCredit('ft. Someone'));
        $this->assertTrue($parser->isCredit('produced by Rick Rubin'));
        $this->assertTrue($parser->isCredit('Explicit'));
        $this->assertTrue($parser->isCredit('mixed by Bob'));
        $this->assertTrue($parser->isCredit('vs. Rival'));
        $this->assertTrue($parser->isCredit('featuring Guest'));
        $this->assertFalse($parser->isCredit('Instrumental'));
        $this->assertFalse($parser->isCredit('Deluxe Edition'));
    }

    public function test_it_does_not_empty_the_title_when_everything_would_be_stripped(): void
    {
        $parsed = app(ParseTrackTitleTask::class)->run('(feat. Only)');

        $this->assertSame('(feat. Only)', $parsed['name']);
        $this->assertNull($parsed['credits']);
        $this->assertSame([], $parsed['featured_artists']);
    }
}
