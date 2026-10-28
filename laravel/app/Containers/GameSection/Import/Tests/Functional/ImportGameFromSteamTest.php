<?php

declare(strict_types=1);

namespace App\Containers\GameSection\Import\Tests\Functional;

use App\Containers\AppSection\User\Models\User;
use App\Containers\GameSection\Game\Models\Game;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ImportGameFromSteamTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        config()->set('steam_store.base_url', 'https://store.steampowered.com');
        config()->set('steam_store.app_path', '/app/%d');
        config()->set('steam_store.locale', 'english');
        config()->set('steam_store.retries', 1);
        config()->set('steam_store.retry_sleep_ms', 0);
    }

    public function test_guest_cannot_import_game(): void
    {
        $this->postJson('/api/v1/games/import', ['steam_id' => 892970])->assertUnauthorized();
    }

    public function test_imports_game_from_store_page_and_updates_it_on_repeat(): void
    {
        Http::fake([
            'https://store.steampowered.com/app/892970*' => Http::sequence()
                ->push($this->storeHtml('Valheim', 'Feb 2, 2021'), 200)
                ->push($this->storeHtml('Valheim Updated', 'Feb 2, 2021'), 200),
        ]);

        $created = $this->actingAs($this->user, 'api')
            ->postJson('/api/v1/games/import', ['steam_id' => 892970]);

        $created->assertCreated()
            ->assertJsonPath('data.type', 'games')
            ->assertJsonPath('data.attributes.name', 'Valheim')
            ->assertJsonPath('data.attributes.steam_id', 892970)
            ->assertJsonPath('data.attributes.air_date', '2021-02-02')
            ->assertJsonPath('data.attributes.developer', 'Iron Gate AB')
            ->assertJsonPath('data.attributes.publisher', 'Coffee Stain Publishing')
            ->assertJsonPath('meta.was_created', true)
            ->assertJsonPath('meta.source_url', 'https://store.steampowered.com/app/892970?l=english');

        $this->assertDatabaseHas('games', [
            'steam_id' => 892970,
            'name' => 'Valheim',
            'img' => 'https://shared.akamai.steamstatic.com/store_item_assets/steam/apps/892970/header.jpg',
        ]);

        $this->actingAs($this->user, 'api')
            ->postJson('/api/v1/games/import', ['steam_id' => 892970])
            ->assertOk()
            ->assertJsonPath('data.attributes.name', 'Valheim Updated')
            ->assertJsonPath('meta.was_created', false);

        $this->assertSame(1, Game::query()->count());

        Http::assertSent(function (Request $request): bool {
            $cookie = implode(' ', $request->header('Cookie'));

            return $request->url() === 'https://store.steampowered.com/app/892970?l=english'
                && str_contains($cookie, 'wants_mature_content=1');
        });
    }

    public function test_missing_store_page_returns_not_found(): void
    {
        Http::fake([
            'https://store.steampowered.com/app/1*' => Http::response('<html><body>Not found</body></html>', 404),
        ]);

        $this->actingAs($this->user, 'api')
            ->postJson('/api/v1/games/import', ['steam_id' => 1])
            ->assertNotFound()
            ->assertJsonPath('message', 'Steam game 1 was not found.');

        $this->assertDatabaseCount('games', 0);
    }

    private function storeHtml(string $name, string $date): string
    {
        return <<<HTML
            <div class="apphub_AppName">{$name}</div>
            <div class="game_description_snippet">A brutal exploration and survival game.</div>
            <img class="game_header_image_full" src="https://shared.akamai.steamstatic.com/store_item_assets/steam/apps/892970/header.jpg">
            <div class="release_date"><div class="date">{$date}</div></div>
            <div class="dev_row">
                <div class="subtitle column">Developer:</div>
                <div class="summary column"><a>Iron Gate AB</a></div>
            </div>
            <div class="dev_row">
                <div class="subtitle column">Publisher:</div>
                <div class="summary column"><a>Coffee Stain Publishing</a></div>
            </div>
        HTML;
    }
}
