<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Post\Tests\Functional;

use App\Containers\AppSection\User\Models\User;
use App\Containers\GallerySection\Album\Models\Album;
use App\Containers\LifelogSection\Post\Enums\PostContentTypeEnum;
use App\Containers\LifelogSection\Post\Models\Post;
use App\Containers\MovieSection\Movie\Models\Movie;
use App\Containers\TaskManagerSection\Task\Models\Task;
use App\Ship\Enums\ContainerAliasEnum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttachTaskAndAlbumToPostTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_post_with_task(): void
    {
        $user = User::factory()->create();
        $task = Task::create([
            'user_id' => $user->id,
            'title' => 'Lifelog task',
        ]);

        $response = $this->actingAs($user, 'api')
            ->postJson('/api/v1/lifelog/posts', [
                'title' => 'Task post',
                'content_type' => PostContentTypeEnum::TASK->value,
                'date' => '2026-10-21',
                'task_id' => $task->id,
                'tags' => [],
                'new_tags' => [],
            ]);

        $response->assertOk()
            ->assertJsonPath('data.attributes.content_type', PostContentTypeEnum::TASK->value)
            ->assertJsonPath('data.relationships.task.data.type', ContainerAliasEnum::TM_TASK->value)
            ->assertJsonPath('data.relationships.task.data.id', (string) $task->id);

        $this->assertDatabaseHas('lifelog_post_subjectables', [
            'subjectable_type' => ContainerAliasEnum::TM_TASK->value,
            'subjectable_id' => $task->id,
        ]);
    }

    public function test_user_can_create_post_with_album(): void
    {
        $user = User::factory()->create();
        $album = Album::create([
            'user_id' => $user->id,
            'name' => 'Travel',
        ]);

        $response = $this->actingAs($user, 'api')
            ->postJson('/api/v1/lifelog/posts', [
                'title' => 'Album post',
                'content_type' => PostContentTypeEnum::ALBUM->value,
                'date' => '2026-10-21',
                'album_id' => $album->id,
                'tags' => [],
                'new_tags' => [],
            ]);

        $response->assertOk()
            ->assertJsonPath('data.attributes.content_type', PostContentTypeEnum::ALBUM->value)
            ->assertJsonPath('data.relationships.album.data.type', ContainerAliasEnum::GALLERY_ALBUM->value)
            ->assertJsonPath('data.relationships.album.data.id', (string) $album->id);

        $this->assertDatabaseHas('lifelog_post_subjectables', [
            'subjectable_type' => ContainerAliasEnum::GALLERY_ALBUM->value,
            'subjectable_id' => $album->id,
        ]);
    }

    public function test_cannot_attach_another_users_task(): void
    {
        $user = User::factory()->create();
        $foreignTask = Task::create([
            'user_id' => User::factory()->create()->id,
            'title' => 'Foreign task',
        ]);

        $response = $this->actingAs($user, 'api')
            ->postJson('/api/v1/lifelog/posts', [
                'title' => 'Task post',
                'content_type' => PostContentTypeEnum::TASK->value,
                'date' => '2026-10-21',
                'task_id' => $foreignTask->id,
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['task_id']);
    }

    public function test_cannot_attach_another_users_album(): void
    {
        $user = User::factory()->create();
        $foreignAlbum = Album::create([
            'user_id' => User::factory()->create()->id,
            'name' => 'Foreign album',
        ]);

        $response = $this->actingAs($user, 'api')
            ->postJson('/api/v1/lifelog/posts', [
                'title' => 'Album post',
                'content_type' => PostContentTypeEnum::ALBUM->value,
                'date' => '2026-10-21',
                'album_id' => $foreignAlbum->id,
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['album_id']);
    }

    public function test_cannot_attach_system_album(): void
    {
        $user = User::factory()->create();
        $systemAlbum = Album::withoutGlobalScopes()
            ->whereNotNull('system_code')
            ->whereNull('user_id')
            ->firstOrFail();

        $response = $this->actingAs($user, 'api')
            ->postJson('/api/v1/lifelog/posts', [
                'title' => 'System album post',
                'content_type' => PostContentTypeEnum::ALBUM->value,
                'date' => '2026-10-21',
                'album_id' => $systemAlbum->id,
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['album_id'])
            ->assertJsonPath(
                'errors.album_id.0',
                'Системные альбомы нельзя прикреплять к посту.'
            );
    }

    public function test_changing_content_type_from_movie_to_task_replaces_subject(): void
    {
        $user = User::factory()->create();
        $movie = Movie::factory()->create();
        $task = Task::create([
            'user_id' => $user->id,
            'title' => 'Replacement task',
        ]);

        $post = Post::factory()->create([
            'user_id' => $user->id,
            'content_type' => PostContentTypeEnum::MOVIE,
            'title' => 'Movie post',
        ]);
        $post->attachMovie($movie);

        $response = $this->actingAs($user, 'api')
            ->patchJson("/api/v1/lifelog/posts/{$post->id}", [
                'content_type' => PostContentTypeEnum::TASK->value,
                'task_id' => $task->id,
            ]);

        $response->assertOk()
            ->assertJsonPath('data.attributes.content_type', PostContentTypeEnum::TASK->value)
            ->assertJsonPath('data.relationships.task.data.id', (string) $task->id);

        $this->assertDatabaseMissing('lifelog_post_subjectables', [
            'post_id' => $post->id,
            'subjectable_type' => ContainerAliasEnum::MOVIE->value,
            'subjectable_id' => $movie->id,
        ]);
        $this->assertDatabaseHas('lifelog_post_subjectables', [
            'post_id' => $post->id,
            'subjectable_type' => ContainerAliasEnum::TM_TASK->value,
            'subjectable_id' => $task->id,
        ]);
    }

    public function test_changing_content_type_to_default_detaches_subjects(): void
    {
        $user = User::factory()->create();
        $album = Album::create([
            'user_id' => $user->id,
            'name' => 'Detach me',
        ]);

        $post = Post::factory()->create([
            'user_id' => $user->id,
            'content_type' => PostContentTypeEnum::ALBUM,
            'title' => 'Album post',
        ]);
        $post->attachAlbum($album);

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

    public function test_deleting_task_removes_post_subject_rows(): void
    {
        $user = User::factory()->create();
        $task = Task::create([
            'user_id' => $user->id,
            'title' => 'Delete me',
        ]);

        $post = Post::factory()->create([
            'user_id' => $user->id,
            'content_type' => PostContentTypeEnum::TASK,
        ]);
        $post->attachTask($task);

        $task->delete();

        $this->assertDatabaseMissing('lifelog_post_subjectables', [
            'subjectable_type' => ContainerAliasEnum::TM_TASK->value,
            'subjectable_id' => $task->id,
        ]);
    }

    public function test_deleting_album_removes_post_subject_rows(): void
    {
        $user = User::factory()->create();
        $album = Album::create([
            'user_id' => $user->id,
            'name' => 'Delete me',
        ]);

        $post = Post::factory()->create([
            'user_id' => $user->id,
            'content_type' => PostContentTypeEnum::ALBUM,
        ]);
        $post->attachAlbum($album);

        $album->delete();

        $this->assertDatabaseMissing('lifelog_post_subjectables', [
            'subjectable_type' => ContainerAliasEnum::GALLERY_ALBUM->value,
            'subjectable_id' => $album->id,
        ]);
    }
}
