<?php

declare(strict_types=1);

namespace App\Containers\GameSection\Import\Tests\Unit;

use App\Containers\GameSection\Import\Exceptions\SteamStoreGameNotFoundException;
use App\Containers\GameSection\Import\Exceptions\SteamStoreParseException;
use App\Containers\GameSection\Import\Support\SteamReleaseDate;
use App\Containers\GameSection\Import\Support\SteamStorePageParser;
use PHPUnit\Framework\TestCase;

class SteamStorePageParserTest extends TestCase
{
    public function test_parses_store_page_fields(): void
    {
        $parsed = (new SteamStorePageParser)->parse(892970, $this->valheimHtml(), 'https://store.steampowered.com/app/892970?l=english');

        $this->assertSame(892970, $parsed->steam_id);
        $this->assertSame('Valheim', $parsed->name);
        $this->assertSame('A brutal exploration and survival game.', $parsed->description);
        $this->assertSame('https://shared.akamai.steamstatic.com/store_item_assets/steam/apps/892970/header.jpg', $parsed->img);
        $this->assertSame('2021-02-02', $parsed->air_date);
        $this->assertSame('Iron Gate AB, Someone Else', $parsed->developer);
        $this->assertSame('Coffee Stain Publishing', $parsed->publisher);
    }

    public function test_coming_soon_release_date_is_empty(): void
    {
        $html = <<<'HTML'
            <div class="apphub_AppName">Upcoming</div>
            <div class="release_date"><div class="date">Coming soon</div></div>
        HTML;

        $parsed = (new SteamStorePageParser)->parse(1, $html, 'https://store.steampowered.com/app/1');

        $this->assertNull($parsed->air_date);
        $this->assertSame('Upcoming', $parsed->name);
    }

    public function test_falls_back_to_open_graph_title_and_image(): void
    {
        $html = <<<'HTML'
            <meta property="og:title" content="Save 20% on Valheim on Steam">
            <meta property="og:image" content="//cdn.example/header.jpg">
            <meta property="og:description" content="Short description.">
        HTML;

        $parsed = (new SteamStorePageParser)->parse(892970, $html, 'https://store.steampowered.com/app/892970');

        $this->assertSame('Valheim', $parsed->name);
        $this->assertSame('https://cdn.example/header.jpg', $parsed->img);
        $this->assertSame('Short description.', $parsed->description);
    }

    public function test_missing_game_throws_not_found(): void
    {
        $this->expectException(SteamStoreGameNotFoundException::class);

        (new SteamStorePageParser)->parse(1, '<html><body>Sorry, we could not find that game.</body></html>', 'https://store.steampowered.com/app/1');
    }

    public function test_age_gate_throws_parse_exception(): void
    {
        $this->expectException(SteamStoreParseException::class);

        (new SteamStorePageParser)->parse(1, '<div id="agegate_box">Enter your birth date</div>', 'https://store.steampowered.com/app/1');
    }

    public function test_release_date_formats(): void
    {
        $this->assertSame('2021-02-02', SteamReleaseDate::toDateString('Feb 2, 2021'));
        $this->assertSame('2021-02-02', SteamReleaseDate::toDateString('2 Feb, 2021'));
        $this->assertNull(SteamReleaseDate::toDateString('To be announced'));
        $this->assertNull(SteamReleaseDate::toDateString(null));
    }

    private function valheimHtml(): string
    {
        return <<<'HTML'
            <html>
            <body>
                <div id="appHubAppName" class="apphub_AppName">Valheim</div>
                <div class="game_description_snippet">
                    A brutal exploration and survival game.
                </div>
                <img class="game_header_image_full" src="https://shared.akamai.steamstatic.com/store_item_assets/steam/apps/892970/header.jpg">
                <div class="release_date">
                    <div class="subtitle column">Release Date:</div>
                    <div class="date">Feb 2, 2021</div>
                </div>
                <div class="dev_row">
                    <div class="subtitle column">Developer:</div>
                    <div class="summary column" id="developers_list">
                        <a href="https://store.steampowered.com/developer/irongate">Iron Gate AB</a>,
                        <a href="https://store.steampowered.com/developer/else">Someone Else</a>
                    </div>
                </div>
                <div class="dev_row">
                    <div class="subtitle column">Publisher:</div>
                    <div class="summary column">
                        <a href="https://store.steampowered.com/publisher/CoffeeStain">Coffee Stain Publishing</a>
                    </div>
                </div>
            </body>
            </html>
        HTML;
    }
}
