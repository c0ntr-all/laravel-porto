<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Post\Tests\Functional;

use App\Containers\AppSection\User\Models\User;
use App\Containers\LifelogSection\Post\Enums\PostContentTypeEnum;
use App\Containers\LifelogSection\Post\Models\Post;
use App\Containers\NotesSection\Note\Models\Note;
use App\Containers\TaskManagerSection\Task\Models\Task;
use App\Ship\Enums\ContainerAliasEnum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttachNoteToPostTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_post_with_note(): void
    {
        $user = User::factory()->create();
        $note = Note::factory()->create([
            'user_id' => $user->id,
            'name' => 'Идея на выходные',
            'content' => '<p>Короткий текст заметки</p>',
        ]);

        $response = $this->actingAs($user, 'api')
            ->postJson('/api/v1/lifelog/posts', [
                'title' => 'Note post',
                'content_type' => PostContentTypeEnum::NOTE->value,
                'date' => '2026-11-06',
                'note_id' => $note->id,
                'tags' => [],
                'new_tags' => [],
            ]);

        $response->assertOk()
            ->assertJsonPath('data.attributes.content_type', PostContentTypeEnum::NOTE->value)
            ->assertJsonPath('data.relationships.note.data.type', ContainerAliasEnum::NOTE->value)
            ->assertJsonPath('data.relationships.note.data.id', (string) $note->id);

        $includedNote = collect($response->json('included'))
            ->firstWhere('type', ContainerAliasEnum::NOTE->value);

        $this->assertNotNull($includedNote);
        $this->assertSame('Идея на выходные', $includedNote['attributes']['name']);
        $this->assertSame('Короткий текст заметки', $includedNote['attributes']['excerpt']);

        $this->assertDatabaseHas('lifelog_post_subjectables', [
            'subjectable_type' => ContainerAliasEnum::NOTE->value,
            'subjectable_id' => $note->id,
        ]);
    }

    public function test_cannot_attach_another_users_note(): void
    {
        $user = User::factory()->create();
        $foreignNote = Note::factory()->create([
            'user_id' => User::factory()->create()->id,
            'name' => 'Чужая заметка',
        ]);

        $response = $this->actingAs($user, 'api')
            ->postJson('/api/v1/lifelog/posts', [
                'title' => 'Note post',
                'content_type' => PostContentTypeEnum::NOTE->value,
                'date' => '2026-11-06',
                'note_id' => $foreignNote->id,
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['note_id']);
    }

    public function test_cannot_attach_deleted_note(): void
    {
        $user = User::factory()->create();
        $note = Note::factory()->create([
            'user_id' => $user->id,
            'name' => 'Удалённая',
        ]);
        $note->delete();

        $response = $this->actingAs($user, 'api')
            ->postJson('/api/v1/lifelog/posts', [
                'title' => 'Note post',
                'content_type' => PostContentTypeEnum::NOTE->value,
                'date' => '2026-11-06',
                'note_id' => $note->id,
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['note_id']);
    }

    public function test_changing_content_type_from_task_to_note_replaces_subject(): void
    {
        $user = User::factory()->create();
        $task = Task::create([
            'user_id' => $user->id,
            'title' => 'Старая задача',
        ]);
        $note = Note::factory()->create([
            'user_id' => $user->id,
            'name' => 'Новая заметка',
        ]);

        $post = Post::factory()->create([
            'user_id' => $user->id,
            'content_type' => PostContentTypeEnum::TASK,
            'title' => 'Task post',
        ]);
        $post->attachTask($task);

        $response = $this->actingAs($user, 'api')
            ->patchJson("/api/v1/lifelog/posts/{$post->id}", [
                'content_type' => PostContentTypeEnum::NOTE->value,
                'note_id' => $note->id,
            ]);

        $response->assertOk()
            ->assertJsonPath('data.attributes.content_type', PostContentTypeEnum::NOTE->value)
            ->assertJsonPath('data.relationships.note.data.id', (string) $note->id);

        $this->assertDatabaseMissing('lifelog_post_subjectables', [
            'post_id' => $post->id,
            'subjectable_type' => ContainerAliasEnum::TM_TASK->value,
            'subjectable_id' => $task->id,
        ]);
        $this->assertDatabaseHas('lifelog_post_subjectables', [
            'post_id' => $post->id,
            'subjectable_type' => ContainerAliasEnum::NOTE->value,
            'subjectable_id' => $note->id,
        ]);
    }

    public function test_changing_content_type_to_default_detaches_note(): void
    {
        $user = User::factory()->create();
        $note = Note::factory()->create([
            'user_id' => $user->id,
            'name' => 'Открепить',
        ]);

        $post = Post::factory()->create([
            'user_id' => $user->id,
            'content_type' => PostContentTypeEnum::NOTE,
            'title' => 'Note post',
        ]);
        $post->attachNote($note);

        $response = $this->actingAs($user, 'api')
            ->patchJson("/api/v1/lifelog/posts/{$post->id}", [
                'content_type' => PostContentTypeEnum::DEFAULT->value,
            ]);

        $response->assertOk()
            ->assertJsonPath('data.attributes.content_type', PostContentTypeEnum::DEFAULT->value);

        $this->assertDatabaseMissing('lifelog_post_subjectables', [
            'post_id' => $post->id,
        ]);
    }
}
