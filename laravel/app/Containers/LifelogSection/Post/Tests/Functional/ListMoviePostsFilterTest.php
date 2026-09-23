<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Post\Tests\Functional;

use App\Containers\AppSection\User\Models\User;
use App\Containers\LifelogSection\Post\Enums\PostContentTypeEnum;
use App\Containers\LifelogSection\Post\Models\Post;
use App\Containers\MovieSection\Movie\Models\Movie;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListMoviePostsFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_lists_current_user_movie_posts_for_given_movie(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $movie = Movie::factory()->create();
        $otherMovie = Movie::factory()->create();

        $withTime = Post::factory()->create([
            'user_id' => $user->id,
            'title' => 'Первый просмотр',
            'content' => 'Дома',
            'content_type' => PostContentTypeEnum::MOVIE,
            'date' => '2026-01-10',
            'time' => '21:15:00',
        ]);
        $withTime->attachMovie($movie);

        $withoutTime = Post::factory()->create([
            'user_id' => $user->id,
            'title' => 'Повтор',
            'content' => 'В поезде',
            'content_type' => PostContentTypeEnum::MOVIE,
            'date' => '2026-02-02',
            'time' => null,
        ]);
        $withoutTime->attachMovie($movie);

        $otherMoviePost = Post::factory()->create([
            'user_id' => $user->id,
            'content_type' => PostContentTypeEnum::MOVIE,
            'date' => '2026-03-01',
        ]);
        $otherMoviePost->attachMovie($otherMovie);

        Post::factory()->create([
            'user_id' => $user->id,
            'content_type' => PostContentTypeEnum::DEFAULT,
            'date' => '2026-01-10',
        ]);

        $foreign = Post::factory()->create([
            'user_id' => $otherUser->id,
            'content_type' => PostContentTypeEnum::MOVIE,
            'date' => '2026-01-10',
        ]);
        $foreign->attachMovie($movie);

        $response = $this->actingAs($user, 'api')
            ->getJson('/api/v1/lifelog/posts?sort=-date&filter[content_type]=movie&filter[movie_id]=' . $movie->id);

        $response->assertOk()
            ->assertJsonPath('meta.count', 2)
            ->assertJsonPath('data.0.attributes.title', 'Повтор')
            ->assertJsonPath('data.0.attributes.date', '2026-02-02')
            ->assertJsonPath('data.0.attributes.time', null)
            ->assertJsonPath('data.0.attributes.content', 'В поезде')
            ->assertJsonPath('data.1.attributes.title', 'Первый просмотр')
            ->assertJsonPath('data.1.attributes.date', '2026-01-10')
            ->assertJsonPath('data.1.attributes.time', '21:15')
            ->assertJsonPath('data.1.attributes.content', 'Дома');
    }
}
