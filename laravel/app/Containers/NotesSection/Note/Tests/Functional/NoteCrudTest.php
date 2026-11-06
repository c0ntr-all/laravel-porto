<?php

declare(strict_types=1);

namespace App\Containers\NotesSection\Note\Tests\Functional;

use App\Containers\AppSection\User\Models\User;
use App\Containers\NotesSection\Note\Models\Note;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Enums\EventTypesEnum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class NoteCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_user_can_create_note_and_html_is_sanitized(): void
    {
        $response = $this->actingAs($this->user, 'api')
            ->postJson('/api/v1/notes', [
                'name' => '  Идеи  ',
                'content' => '<p>Текст <b onclick="alert(1)">жирный</b></p><script>alert(1)</script>',
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.attributes.name', 'Идеи')
            ->assertJsonPath('data.type', 'notes');

        $content = (string) $response->json('data.attributes.content');
        $this->assertStringContainsString('Текст', $content);
        $this->assertStringContainsString('жирный', $content);
        $this->assertStringNotContainsString('script', strtolower($content));
        $this->assertStringNotContainsString('onclick', strtolower($content));
        $this->assertSame('Текст жирный', $response->json('data.attributes.content_raw'));
        $this->assertSame('Текст жирный', $response->json('data.attributes.excerpt'));
        $this->assertFalse($response->json('data.attributes.is_pinned'));
        $this->assertNull($response->json('data.attributes.parent_id'));

        $this->assertDatabaseHas('notes', [
            'name' => 'Идеи',
            'user_id' => $this->user->id,
        ]);

        $this->assertSame(1, DB::table('activity_use_case_logs')->where([
            'user_id' => $this->user->id,
            'loggable_type' => ContainerAliasEnum::NOTE->value,
            'loggable_id' => (string) $response->json('data.id'),
            'event_type' => EventTypesEnum::CREATED->value,
        ])->count());
    }

    public function test_user_can_list_search_and_open_own_note(): void
    {
        $older = Note::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Старая',
            'content' => '<p>архив</p>',
        ]);
        Note::query()->whereKey($older->id)->update([
            'updated_at' => now()->subDay(),
        ]);
        Note::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Свежая',
            'content' => '<p>поиск по слову маяк</p>',
        ]);
        Note::factory()->create([
            'user_id' => User::factory(),
            'name' => 'Чужая маяк',
            'content' => '<p>не должна находиться</p>',
        ]);

        $list = $this->actingAs($this->user, 'api')
            ->getJson('/api/v1/notes');

        $list->assertOk()
            ->assertJsonPath('meta.per_page', 20)
            ->assertJsonPath('meta.has_more', false)
            ->assertJsonPath('data.0.attributes.name', 'Свежая')
            ->assertJsonCount(2, 'data');

        $this->actingAs($this->user, 'api')
            ->getJson('/api/v1/notes?filter[text]=маяк')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.attributes.name', 'Свежая');

        $this->actingAs($this->user, 'api')
            ->getJson('/api/v1/notes?filter[text]='.rawurlencode('<p>'))
            ->assertOk()
            ->assertJsonCount(0, 'data');

        $this->actingAs($this->user, 'api')
            ->getJson('/api/v1/notes/'.$older->id)
            ->assertOk()
            ->assertJsonPath('data.attributes.name', 'Старая')
            ->assertJsonPath('data.attributes.content', '<p>архив</p>')
            ->assertJsonPath('data.attributes.content_raw', 'архив');
    }

    public function test_user_can_update_name_without_wiping_content(): void
    {
        $note = Note::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Черновик',
            'content' => '<p>оставить</p>',
        ]);
        $note->refresh();

        $this->actingAs($this->user, 'api')
            ->patchJson('/api/v1/notes/'.$note->id, [
                'name' => 'Готово',
                'updated_at' => $note->timestamp(),
            ])
            ->assertOk()
            ->assertJsonPath('data.attributes.name', 'Готово')
            ->assertJsonPath('data.attributes.content', '<p>оставить</p>');
    }

    public function test_user_can_soft_delete_note(): void
    {
        $note = Note::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Удалить',
        ]);

        $this->actingAs($this->user, 'api')
            ->deleteJson('/api/v1/notes/'.$note->id)
            ->assertOk()
            ->assertJsonPath('meta.message', 'Note successfully deleted!');

        $this->assertSoftDeleted('notes', ['id' => $note->id]);

        $this->assertSame(1, DB::table('activity_use_case_logs')->where([
            'user_id' => $this->user->id,
            'loggable_type' => ContainerAliasEnum::NOTE->value,
            'loggable_id' => (string) $note->id,
            'event_type' => EventTypesEnum::DELETED->value,
        ])->count());

        $this->actingAs($this->user, 'api')
            ->getJson('/api/v1/notes/'.$note->id)
            ->assertNotFound();
    }

    public function test_name_is_required(): void
    {
        $this->actingAs($this->user, 'api')
            ->postJson('/api/v1/notes', ['content' => '<p>без имени</p>'])
            ->assertUnprocessable();
    }

    public function test_guests_cannot_create_or_list_notes(): void
    {
        $this->postJson('/api/v1/notes', ['name' => 'Секрет'])->assertUnauthorized();
        $this->getJson('/api/v1/notes')->assertUnauthorized();
    }

    public function test_user_cannot_read_or_change_another_users_note(): void
    {
        $note = Note::factory()->create([
            'user_id' => User::factory(),
            'name' => 'Секрет',
        ]);

        $this->actingAs($this->user, 'api')
            ->getJson('/api/v1/notes/'.$note->id)
            ->assertNotFound();

        $this->actingAs($this->user, 'api')
            ->patchJson('/api/v1/notes/'.$note->id, ['name' => 'Взлом'])
            ->assertNotFound();

        $this->actingAs($this->user, 'api')
            ->deleteJson('/api/v1/notes/'.$note->id)
            ->assertNotFound();

        $this->assertDatabaseHas('notes', [
            'id' => $note->id,
            'name' => 'Секрет',
            'deleted_at' => null,
        ]);
    }
}
