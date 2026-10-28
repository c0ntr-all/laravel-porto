<?php

declare(strict_types=1);

namespace App\Containers\GameSection\Import\Tests\Functional;

use App\Containers\AppSection\User\Models\User;
use App\Containers\GameSection\Game\Models\Game;
use App\Containers\GameSection\Import\Enums\GameImportStatusEnum;
use App\Containers\GameSection\Import\Models\GameImport;
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
        $this->postJson('/api/v1/games/imports', ['steam_id' => 892970])->assertUnauthorized();
    }

    public function test_imports_game_from_store_page_and_updates_it_on_repeat(): void
    {
        Http::fake([
            'https://store.steampowered.com/app/892970*' => Http::sequence()
                ->push($this->storeHtml('Valheim', 'Feb 2, 2021'), 200)
                ->push($this->storeHtml('Valheim Updated', 'Feb 2, 2021'), 200),
        ]);

        $created = $this->actingAs($this->user, 'api')
            ->postJson('/api/v1/games/imports', ['steam_id' => 892970]);

        $created->assertCreated()
            ->assertJsonPath('data.type', 'game_imports')
            ->assertJsonPath('data.attributes.status', GameImportStatusEnum::Completed->value)
            ->assertJsonPath('data.attributes.steam_id', 892970)
            ->assertJsonPath('data.attributes.was_created', true)
            ->assertJsonPath('data.attributes.source_url', 'https://store.steampowered.com/app/892970?l=english')
            ->assertJsonPath('data.meta.source', 'steam')
            ->assertJsonPath('data.meta.action', 'created')
            ->assertJsonPath('data.meta.duplicate', false)
            ->assertJsonPath('data.meta.after.name', 'Valheim')
            ->assertJsonPath('data.meta.after.air_date', '2021-02-02')
            ->assertJsonPath('data.meta.after.developer', 'Iron Gate AB')
            ->assertJsonPath('data.meta.after.publisher', 'Coffee Stain Publishing')
            ->assertJsonPath('meta.message', 'Game imported successfully!');

        $this->assertNull($created->json('data.meta.before'));

        $included = collect($created->json('included'))->firstWhere('type', 'games');
        $this->assertIsArray($included);
        $this->assertSame('Valheim', $included['attributes']['name']);

        $this->assertDatabaseHas('games', [
            'steam_id' => 892970,
            'name' => 'Valheim',
            'img' => 'https://shared.akamai.steamstatic.com/store_item_assets/steam/apps/892970/header.jpg',
        ]);
        $this->assertDatabaseHas('game_imports', [
            'user_id' => $this->user->id,
            'steam_id' => 892970,
            'status' => GameImportStatusEnum::Completed->value,
            'was_created' => true,
        ]);

        $updated = $this->actingAs($this->user, 'api')
            ->postJson('/api/v1/games/imports', ['steam_id' => 892970]);

        $updated->assertCreated()
            ->assertJsonPath('data.attributes.was_created', false)
            ->assertJsonPath('data.meta.action', 'updated')
            ->assertJsonPath('data.meta.duplicate', true)
            ->assertJsonPath('data.meta.before.name', 'Valheim')
            ->assertJsonPath('data.meta.after.name', 'Valheim Updated')
            ->assertJsonPath('data.meta.changes.name.old', 'Valheim')
            ->assertJsonPath('data.meta.changes.name.new', 'Valheim Updated')
            ->assertJsonPath('meta.message', 'Game updated from Steam.');

        $this->assertSame(1, Game::query()->count());
        $this->assertSame(2, GameImport::query()->count());

        Http::assertSent(function (Request $request): bool {
            $cookie = implode(' ', $request->header('Cookie'));

            return $request->url() === 'https://store.steampowered.com/app/892970?l=english'
                && str_contains($cookie, 'wants_mature_content=1');
        });
    }

    public function test_missing_store_page_is_logged_as_failed(): void
    {
        Http::fake([
            'https://store.steampowered.com/app/1*' => Http::response('<html><body>Not found</body></html>', 404),
        ]);

        $this->actingAs($this->user, 'api')
            ->postJson('/api/v1/games/imports', ['steam_id' => 1])
            ->assertNotFound()
            ->assertJsonPath('data.type', 'game_imports')
            ->assertJsonPath('data.attributes.status', GameImportStatusEnum::Failed->value)
            ->assertJsonPath('data.attributes.http_status', 404)
            ->assertJsonPath('data.meta.reason', 'not_found')
            ->assertJsonPath('data.meta.steam.http_status', 404);

        $this->assertDatabaseCount('games', 0);
        $this->assertDatabaseHas('game_imports', [
            'user_id' => $this->user->id,
            'steam_id' => 1,
            'status' => GameImportStatusEnum::Failed->value,
        ]);
    }

    public function test_user_can_list_and_get_own_import_history(): void
    {
        Http::fake([
            'https://store.steampowered.com/app/892970*' => Http::response($this->storeHtml('Valheim', 'Feb 2, 2021'), 200),
        ]);

        $created = $this->actingAs($this->user, 'api')
            ->postJson('/api/v1/games/imports', ['steam_id' => 892970])
            ->assertCreated();

        $importId = (int) $created->json('data.id');
        $other = User::factory()->create();
        GameImport::factory()->create(['user_id' => $other->id, 'steam_id' => 10]);

        $this->actingAs($this->user, 'api')
            ->getJson('/api/v1/games/imports')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.attributes.steam_id', 892970)
            ->assertJsonPath('data.0.attributes.status', GameImportStatusEnum::Completed->value);

        $this->actingAs($this->user, 'api')
            ->getJson('/api/v1/games/imports/'.$importId)
            ->assertOk()
            ->assertJsonPath('data.attributes.steam_id', 892970)
            ->assertJsonPath('data.attributes.game_id', $created->json('data.attributes.game_id'));

        $this->actingAs($other, 'api')
            ->getJson('/api/v1/games/imports/'.$importId)
            ->assertForbidden();

        $this->actingAs($other, 'api')
            ->getJson('/api/v1/games/imports')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.attributes.steam_id', 10);
    }

    public function test_legacy_import_path_writes_the_same_log(): void
    {
        Http::fake([
            'https://store.steampowered.com/app/892970*' => Http::response($this->storeHtml('Valheim', 'Feb 2, 2021'), 200),
        ]);

        $this->actingAs($this->user, 'api')
            ->postJson('/api/v1/games/import', ['steam_id' => 892970])
            ->assertCreated()
            ->assertJsonPath('data.type', 'game_imports')
            ->assertJsonPath('data.attributes.was_created', true);
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
