<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Post\Tests\Functional;

use App\Containers\AppSection\User\Models\User;
use App\Containers\LifelogSection\Post\Data\DTO\PostCreateDto;
use App\Containers\LifelogSection\Post\Enums\PostContentTypeEnum;
use App\Containers\LifelogSection\Post\UI\Actions\CreatePostAction;
use App\Containers\MovieSection\Folder\Enums\SystemMovieFolderEnum;
use App\Containers\MovieSection\Folder\Models\Folder;
use App\Containers\MovieSection\Movie\Models\Movie;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateMoviePostAddsToWatchedFolderTest extends TestCase
{
    use RefreshDatabase;

    public function test_movie_post_with_movie_id_adds_film_to_watched_folder(): void
    {
        $user = User::factory()->create();
        $movie = Movie::factory()->create();

        app(CreatePostAction::class)->handle(PostCreateDto::from([
            'user_id' => $user->id,
            'title' => 'Посмотрел фильм',
            'content_type' => PostContentTypeEnum::MOVIE,
            'date' => '2026-09-21',
            'movie_id' => $movie->id,
            'new_tags' => [],
            'tags' => [],
            'attachments' => [],
        ]));

        $folder = Folder::query()
            ->where('user_id', $user->id)
            ->where('slug', SystemMovieFolderEnum::WATCHED->value)
            ->firstOrFail();

        $this->assertDatabaseHas('movie_folder_movie', [
            'folder_id' => $folder->id,
            'movie_id' => $movie->id,
        ]);
        $this->assertSame(1, (int) $folder->refresh()->movies_count);
    }

    public function test_does_not_duplicate_movie_already_in_watched_folder(): void
    {
        $user = User::factory()->create();
        $movie = Movie::factory()->create();
        $folder = Folder::query()
            ->where('user_id', $user->id)
            ->where('slug', SystemMovieFolderEnum::WATCHED->value)
            ->firstOrFail();

        $folder->movies()->attach($movie->id, ['added_at' => now()]);
        $folder->forceFill(['movies_count' => 1])->save();

        app(CreatePostAction::class)->handle(PostCreateDto::from([
            'user_id' => $user->id,
            'title' => 'Ещё раз',
            'content_type' => PostContentTypeEnum::MOVIE,
            'date' => '2026-09-22',
            'movie_id' => $movie->id,
            'new_tags' => [],
            'tags' => [],
            'attachments' => [],
        ]));

        $this->assertSame(1, $folder->movies()->count());
        $this->assertSame(1, (int) $folder->refresh()->movies_count);
    }

    public function test_movie_post_by_title_does_not_add_to_watched_folder(): void
    {
        $user = User::factory()->create();

        app(CreatePostAction::class)->handle(PostCreateDto::from([
            'user_id' => $user->id,
            'title' => 'Новый фильм',
            'content_type' => PostContentTypeEnum::MOVIE,
            'date' => '2026-09-21',
            'movie_title' => 'Unknown Title',
            'new_tags' => [],
            'tags' => [],
            'attachments' => [],
        ]));

        $folder = Folder::query()
            ->where('user_id', $user->id)
            ->where('slug', SystemMovieFolderEnum::WATCHED->value)
            ->firstOrFail();

        $this->assertSame(0, $folder->movies()->count());
    }
}
