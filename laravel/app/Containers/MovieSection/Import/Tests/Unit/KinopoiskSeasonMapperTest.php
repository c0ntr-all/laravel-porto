<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Import\Tests\Unit;

use App\Containers\MovieSection\Import\Support\KinopoiskSeasonMapper;
use PHPUnit\Framework\TestCase;

class KinopoiskSeasonMapperTest extends TestCase
{
    public function test_maps_seasons_and_episodes_payload(): void
    {
        $mapped = (new KinopoiskSeasonMapper())->fromDocs([
            [
                'movieId' => 404900,
                'number' => 1,
                'name' => 'Сезон 1',
                'enName' => 'Season 1',
                'airDate' => '2008-01-20T00:00:00.000Z',
                'episodesCount' => 7,
                'duration' => 47,
                'poster' => [
                    'url' => 'https://example.com/s1.jpg',
                    'previewUrl' => 'https://example.com/s1-preview.jpg',
                ],
                'episodes' => [
                    [
                        'number' => 1,
                        'name' => 'Пилот',
                        'enName' => 'Pilot',
                        'description' => 'Уолтер Уайт узнаёт диагноз.',
                        'enDescription' => 'Walter White learns a diagnosis.',
                        'duration' => 58,
                        'airDate' => '2008-01-20',
                        'still' => [
                            'url' => 'https://example.com/e1.jpg',
                            'previewUrl' => 'https://example.com/e1-preview.jpg',
                        ],
                    ],
                    [
                        'number' => 2,
                        'name' => 'Кот в мешке…',
                        'date' => '2008-01-27',
                    ],
                ],
            ],
        ], 404900);

        $this->assertCount(1, $mapped);
        $season = $mapped[0];
        $this->assertSame(1, $season->number);
        $this->assertSame(404900, $season->kp_movie_id);
        $this->assertSame('Сезон 1', $season->name);
        $this->assertSame('Season 1', $season->en_name);
        $this->assertSame('2008-01-20', $season->air_date);
        $this->assertSame(7, $season->episodes_count);
        $this->assertSame(47, $season->duration);
        $this->assertSame('https://example.com/s1.jpg', $season->poster);
        $this->assertSame('https://example.com/s1-preview.jpg', $season->poster_preview);
        $this->assertCount(2, $season->episodes);
        $this->assertSame('Пилот', $season->episodes[0]->name);
        $this->assertSame('Walter White learns a diagnosis.', $season->episodes[0]->en_description);
        $this->assertSame('https://example.com/e1.jpg', $season->episodes[0]->still);
        $this->assertSame('2008-01-27', $season->episodes[1]->air_date);
    }

    public function test_skips_season_without_number(): void
    {
        $mapped = (new KinopoiskSeasonMapper())->fromDocs([
            ['movieId' => 1, 'name' => 'Broken'],
        ], 1);

        $this->assertSame([], $mapped);
    }
}
