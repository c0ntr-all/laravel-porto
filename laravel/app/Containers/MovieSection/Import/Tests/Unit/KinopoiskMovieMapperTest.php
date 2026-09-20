<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Import\Tests\Unit;

use App\Containers\MovieSection\Import\Exceptions\KinopoiskParseException;
use App\Containers\MovieSection\Import\Support\KinopoiskMovieMapper;
use App\Containers\MovieSection\Movie\Enums\MovieTypeEnum;
use PHPUnit\Framework\TestCase;

class KinopoiskMovieMapperTest extends TestCase
{
    public function test_maps_poiskkino_movie_payload(): void
    {
        $parsed = (new KinopoiskMovieMapper())->fromApi([
            'id' => 325,
            'name' => 'Крестный отец',
            'alternativeName' => 'The Godfather',
            'year' => 1972,
            'type' => 'movie',
            'description' => 'Криминальная сага о семье Корлеоне.',
            'shortDescription' => 'Революция в гангстерском кино.',
            'poster' => ['url' => 'https://example.com/poster.jpg'],
            'rating' => ['kp' => 8.709],
            'genres' => [['id' => 8, 'name' => 'драма']],
            'countries' => [['id' => 1, 'name' => 'США']],
        ], 325);

        $this->assertSame(325, $parsed->kp_id);
        $this->assertSame('Крестный отец', $parsed->title);
        $this->assertSame(1972, $parsed->year);
        $this->assertSame(MovieTypeEnum::MOVIE, $parsed->type);
        $this->assertSame('Криминальная сага о семье Корлеоне.', $parsed->description);
        $this->assertSame('Революция в гангстерском кино.', $parsed->short_description);
        $this->assertSame(8.7, $parsed->kp_rating);
        $this->assertSame('драма', $parsed->genres[0]->name);
        $this->assertSame(8, $parsed->genres[0]->kp_id);
        $this->assertSame(['США'], $parsed->countries);
    }

    public function test_maps_tv_series_type(): void
    {
        $parsed = (new KinopoiskMovieMapper())->fromApi([
            'id' => 404900,
            'name' => 'Во все тяжкие',
            'year' => 2008,
            'type' => 'tv-series',
            'isSeries' => true,
            'genres' => [],
            'countries' => [],
        ], 404900);

        $this->assertSame(MovieTypeEnum::TV_SERIES, $parsed->type);
    }

    public function test_fails_without_title(): void
    {
        $this->expectException(KinopoiskParseException::class);

        (new KinopoiskMovieMapper())->fromApi(['id' => 1, 'year' => 1999], 1);
    }
}
