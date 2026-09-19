<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Import\Tests\Unit;

use App\Containers\MovieSection\Import\Data\DTO\KinopoiskPageDto;
use App\Containers\MovieSection\Import\Exceptions\KinopoiskParseException;
use App\Containers\MovieSection\Import\Support\KinopoiskFilmParser;
use App\Containers\MovieSection\Movie\Enums\MovieTypeEnum;
use PHPUnit\Framework\TestCase;

class KinopoiskFilmParserTest extends TestCase
{
    public function test_parses_json_ld_movie(): void
    {
        $parsed = (new KinopoiskFilmParser())->parse($this->page($this->jsonLdHtml()));

        $this->assertSame(326, $parsed->kp_id);
        $this->assertSame('Криминальное чтиво', $parsed->title);
        $this->assertSame(1994, $parsed->year);
        $this->assertSame(MovieTypeEnum::MOVIE, $parsed->type);
        $this->assertSame('https://avatars.mds.yandex.net/get-kinopoisk-image/cover/orig', $parsed->kp_img);
        $this->assertSame(8.7, $parsed->kp_rating);
        $this->assertSame('Банда налетчиков под предводительством психопата сбегает с добычей.', $parsed->description);
        $this->assertSame(['криминал', 'драма'], array_map(static fn ($genre) => $genre->name, $parsed->genres));
        $this->assertSame(['США'], $parsed->countries);
        $this->assertContains('json_ld', $parsed->parser_sources);
    }

    public function test_parses_next_data_and_fills_genre_ids(): void
    {
        $html = $this->jsonLdHtml().$this->nextDataScript();
        $parsed = (new KinopoiskFilmParser())->parse($this->page($html));

        $this->assertSame(8, $parsed->genres[1]->kp_id);
        $this->assertContains('next_data', $parsed->parser_sources);
    }

    public function test_parses_tv_series_from_next_data_url(): void
    {
        $parsed = (new KinopoiskFilmParser())->parse(new KinopoiskPageDto(
            kp_id: 404900,
            url: 'https://www.kinopoisk.ru/series/404900/',
            http_status: 200,
            html: $this->seriesNextDataHtml(),
            final_url: 'https://www.kinopoisk.ru/series/404900/',
        ));

        $this->assertSame('Во все тяжкие', $parsed->title);
        $this->assertSame(2008, $parsed->year);
        $this->assertSame(MovieTypeEnum::TV_SERIES, $parsed->type);
        $this->assertSame('Учитель химии начинает варить метамфетамин.', $parsed->description);
        $this->assertSame(['США'], $parsed->countries);
    }

    public function test_fails_without_title_and_includes_diagnostics(): void
    {
        try {
            (new KinopoiskFilmParser())->parse($this->page('<html><head><title>Кинопоиск</title></head><body>empty</body></html>'));
            $this->fail('Expected KinopoiskParseException');
        } catch (KinopoiskParseException $exception) {
            $this->assertSame('missing_title', $exception->context['reason']);
            $this->assertSame('Кинопоиск', $exception->context['kinopoisk']['page_title']);
            $this->assertFalse($exception->context['parser']['signals']['has_json_ld']);
            $this->assertArrayHasKey('html_preview', $exception->context['kinopoisk']);
        }
    }

    private function page(string $html): KinopoiskPageDto
    {
        return new KinopoiskPageDto(
            kp_id: 326,
            url: 'https://www.kinopoisk.ru/film/326/',
            http_status: 200,
            html: $html,
            final_url: 'https://www.kinopoisk.ru/film/326/',
        );
    }

    private function jsonLdHtml(): string
    {
        $json = json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'Movie',
            'name' => 'Криминальное чтиво',
            'image' => 'https://avatars.mds.yandex.net/get-kinopoisk-image/cover',
            'dateCreated' => '1994-05-21',
            'genre' => ['криминал', 'драма'],
            'countryOfOrigin' => [['@type' => 'Country', 'name' => 'США']],
            'aggregateRating' => ['@type' => 'AggregateRating', 'ratingValue' => '8.687'],
            'description' => 'Банда налетчиков под предводительством психопата сбегает с добычей.',
        ], JSON_UNESCAPED_UNICODE);

        return '<script type="application/ld+json">'.$json.'</script>';
    }

    private function nextDataScript(): string
    {
        $json = json_encode([
            'props' => [
                'apolloState' => [
                    'Film:326' => [
                        '__typename' => 'Film',
                        'id' => 326,
                        'title' => ['russian' => 'Криминальное чтиво'],
                        'productionYear' => 1994,
                        'genres' => [
                            'items' => [
                                ['id' => 3, 'name' => 'криминал'],
                                ['id' => 8, 'name' => 'драма'],
                            ],
                        ],
                        'countries' => [
                            'items' => [
                                ['id' => 1, 'name' => 'США'],
                            ],
                        ],
                    ],
                ],
            ],
        ], JSON_UNESCAPED_UNICODE);

        return '<script id="__NEXT_DATA__" type="application/json">'.$json.'</script>';
    }

    private function seriesNextDataHtml(): string
    {
        $json = json_encode([
            'props' => [
                'pageProps' => [
                    'film' => [
                        '__typename' => 'TvSeries',
                        'id' => 404900,
                        'title' => ['russian' => 'Во все тяжкие', 'original' => 'Breaking Bad'],
                        'productionYear' => 2008,
                        'genres' => [['name' => 'криминал']],
                        'countries' => [['name' => 'США']],
                        'rating' => ['kinopoisk' => ['value' => 9.0]],
                        'synopsis' => 'Учитель химии начинает варить метамфетамин.',
                        'poster' => ['avatarsUrl' => 'https://avatars.mds.yandex.net/get-kinopoisk-image/bb'],
                    ],
                ],
            ],
        ], JSON_UNESCAPED_UNICODE);

        return '<script id="__NEXT_DATA__" type="application/json">'.$json.'</script>';
    }
}
