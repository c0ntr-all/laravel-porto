<?php

declare(strict_types=1);

namespace App\Containers\NotesSection\Note\Tests\Functional;

use App\Containers\AppSection\User\Models\User;
use App\Containers\DashboardSection\Dashboard\Models\Dashboard;
use App\Containers\NotesSection\Note\Models\Note;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NoteStructureTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_update_requires_current_updated_at_and_rejects_a_stale_tab(): void
    {
        $created = $this->actingAs($this->user, 'api')
            ->postJson('/api/v1/notes', ['name' => 'Черновик'])
            ->assertCreated();

        $id = $created->json('data.id');
        $stamp = $created->json('data.attributes.updated_at');

        $this->actingAs($this->user, 'api')
            ->patchJson('/api/v1/notes/'.$id, ['name' => 'Без штампа'])
            ->assertUnprocessable();

        $this->actingAs($this->user, 'api')
            ->patchJson('/api/v1/notes/'.$id, [
                'name' => 'Первая вкладка',
                'updated_at' => $stamp,
            ])
            ->assertOk()
            ->assertJsonPath('data.attributes.name', 'Первая вкладка');

        $this->actingAs($this->user, 'api')
            ->patchJson('/api/v1/notes/'.$id, [
                'name' => 'Вторая вкладка',
                'updated_at' => $stamp,
            ])
            ->assertStatus(409)
            ->assertJsonPath('message', 'Заметку уже изменили в другой вкладке. Обновите её и повторите сохранение.');

        $this->assertDatabaseHas('notes', [
            'id' => $id,
            'name' => 'Первая вкладка',
        ]);
    }

    public function test_notes_can_be_nested_and_delete_promotes_children(): void
    {
        $rootId = $this->createNote('Корень');
        $childId = $this->createNote('Ребёнок', ['parent_id' => (int) $rootId]);
        $this->createNote('Внук', ['parent_id' => (int) $childId]);

        $this->actingAs($this->user, 'api')
            ->getJson('/api/v1/notes?filter[roots]=1')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.attributes.name', 'Корень')
            ->assertJsonPath('data.0.attributes.children_count', 1);

        $this->actingAs($this->user, 'api')
            ->patchJson('/api/v1/notes/'.$rootId, [
                'parent_id' => (int) $childId,
                'updated_at' => $this->stamp($rootId),
            ])
            ->assertStatus(422);

        $this->actingAs($this->user, 'api')
            ->deleteJson('/api/v1/notes/'.$childId)
            ->assertOk();

        $this->assertDatabaseHas('notes', [
            'id' => $this->idByName('Внук'),
            'parent_id' => $rootId,
        ]);
    }

    public function test_wiki_links_are_stored_on_save_and_survive_a_rename(): void
    {
        $targetId = $this->createNote('Рецепт');
        $source = $this->actingAs($this->user, 'api')
            ->postJson('/api/v1/notes', [
                'name' => 'Ужин',
                'content' => '<p>Смотри [[Рецепт]] и [[Нет такой]]</p>',
            ])
            ->assertCreated();

        $source->assertJsonPath('data.attributes.wiki_links.0.id', (string) $targetId);
        $source->assertJsonPath('data.attributes.wiki_links.0.name', 'Рецепт');
        $source->assertJsonPath('data.attributes.wiki_links.0.label', 'Рецепт');
        $source->assertJsonPath('data.attributes.unresolved_links.0', 'Нет такой');

        $this->actingAs($this->user, 'api')
            ->getJson('/api/v1/notes/'.$targetId)
            ->assertOk()
            ->assertJsonPath('data.attributes.backlinks.0.id', $source->json('data.id'))
            ->assertJsonPath('data.attributes.backlinks.0.name', 'Ужин');

        $this->actingAs($this->user, 'api')
            ->patchJson('/api/v1/notes/'.$targetId, [
                'name' => 'Борщ',
                'updated_at' => $this->stamp($targetId),
            ])
            ->assertOk();

        $this->actingAs($this->user, 'api')
            ->getJson('/api/v1/notes/'.$source->json('data.id'))
            ->assertOk()
            ->assertJsonPath('data.attributes.wiki_links.0.id', (string) $targetId)
            ->assertJsonPath('data.attributes.wiki_links.0.name', 'Борщ');
    }

    public function test_ambiguous_title_links_to_the_newest_note(): void
    {
        $older = $this->createNote('Дубль');
        $newer = $this->createNote('Дубль');

        $response = $this->actingAs($this->user, 'api')
            ->postJson('/api/v1/notes', [
                'name' => 'Источник',
                'content' => '<p>[[Дубль]]</p>',
            ])
            ->assertCreated();

        $response->assertJsonPath('data.attributes.wiki_links.0.id', (string) $newer);
        $response->assertJsonPath('data.attributes.ambiguous_links.0', 'Дубль');
        $this->assertNotSame((string) $older, $response->json('data.attributes.wiki_links.0.id'));
    }

    public function test_pin_order_moves_one_note_without_touching_updated_at(): void
    {
        $first = $this->createNote('Первая');
        $second = $this->createNote('Вторая');
        $third = $this->createNote('Третья');

        $this->pin($first);
        $this->pin($second);
        $this->pin($third);

        $firstNote = Note::query()->findOrFail($first);
        $secondNote = Note::query()->findOrFail($second);
        $thirdNote = Note::query()->findOrFail($third);
        $firstPosition = (int) $firstNote->pin_position;
        $secondPosition = (int) $secondNote->pin_position;
        $thirdStamp = $thirdNote->timestamp();

        $this->actingAs($this->user, 'api')
            ->postJson('/api/v1/notes/'.$third.'/pin-position', ['before_id' => $first])
            ->assertOk();

        $firstNote->refresh();
        $secondNote->refresh();
        $thirdNote->refresh();

        $this->assertSame($firstPosition, (int) $firstNote->pin_position);
        $this->assertSame($secondPosition, (int) $secondNote->pin_position);
        $this->assertLessThan($firstPosition, (int) $thirdNote->pin_position);
        $this->assertSame($thirdStamp, $thirdNote->timestamp());

        $this->actingAs($this->user, 'api')
            ->getJson('/api/v1/notes?filter[pinned]=1&sort=pin_position')
            ->assertOk()
            ->assertJsonPath('data.0.attributes.name', 'Третья')
            ->assertJsonPath('data.1.attributes.name', 'Первая')
            ->assertJsonPath('data.2.attributes.name', 'Вторая');

        $loose = $this->createNote('Свободная');
        $this->actingAs($this->user, 'api')
            ->postJson('/api/v1/notes/'.$loose.'/pin-position', ['before_id' => null])
            ->assertStatus(422);
    }

    public function test_dashboard_widgets_list_recent_and_pinned_notes(): void
    {
        $dashboard = Dashboard::query()->create([
            'user_id' => $this->user->id,
            'name' => 'Главная',
            'is_default' => true,
        ]);

        $older = Note::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Старая',
            'content' => '<p>архив</p>',
        ]);
        Note::query()->whereKey($older->id)->update(['updated_at' => now()->subDay()]);
        $pinnedId = $this->createNote('На виду');
        $this->pin($pinnedId);
        $fresh = Note::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Свежая',
            'content' => '<p>сегодня</p>',
        ]);

        $this->actingAs($this->user, 'api')
            ->getJson('/api/v1/dashboard/widget-types')
            ->assertOk()
            ->assertJsonFragment(['type' => 'notes.recent-notes'])
            ->assertJsonFragment(['type' => 'notes.pinned-notes']);

        $this->actingAs($this->user, 'api')
            ->postJson('/api/v1/dashboards/'.$dashboard->id.'/widgets', [
                'type' => 'notes.recent-notes',
                'size' => 'third',
            ])
            ->assertCreated()
            ->assertJsonPath('data.attributes.payload.data.items.0.title', 'Свежая')
            ->assertJsonPath('data.attributes.payload.meta.href', '/notes')
            ->assertJsonPath('data.attributes.payload.data.items.0.href', '/notes/'.$fresh->id);

        $this->actingAs($this->user, 'api')
            ->postJson('/api/v1/dashboards/'.$dashboard->id.'/widgets', [
                'type' => 'notes.pinned-notes',
                'size' => 'third',
            ])
            ->assertCreated()
            ->assertJsonPath('data.attributes.payload.data.items.0.title', 'На виду')
            ->assertJsonPath('data.attributes.payload.meta.count', 1)
            ->assertJsonPath('data.attributes.payload.meta.href', '/notes')
            ->assertJsonPath('data.attributes.payload.data.items.0.href', '/notes/'.$pinnedId);
    }

    /**
     * @param  array<string, mixed>  $extra
     */
    private function createNote(string $name, array $extra = []): int
    {
        $response = $this->actingAs($this->user, 'api')
            ->postJson('/api/v1/notes', array_merge(['name' => $name], $extra))
            ->assertCreated();

        return (int) $response->json('data.id');
    }

    private function stamp(int $id): string
    {
        return (string) Note::query()->findOrFail($id)->timestamp();
    }

    private function pin(int $id): void
    {
        $this->actingAs($this->user, 'api')
            ->postJson('/api/v1/notes/'.$id.'/pin')
            ->assertOk()
            ->assertJsonPath('data.attributes.is_pinned', true);
    }

    private function idByName(string $name): int
    {
        return (int) Note::query()->where('name', $name)->value('id');
    }
}
