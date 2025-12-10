<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Post\Tests\Unit;

use App\Containers\AppSection\User\Models\User;
use App\Containers\LifelogSection\Post\Data\DTO\PostUpdateDto;
use App\Containers\LifelogSection\Post\Models\Post;
use App\Containers\LifelogSection\Post\Tasks\UpdatePostTask;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdatePostTaskTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_updates_post_correctly(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Post::unsetEventDispatcher();

        $datetime = fake()->dateTimeBetween('-2 months', 'now');

        $post = Post::factory()->create([
            'user_id' => $user->id,
            'title' => 'Old title',
            'content' => 'Old content',
            'date' => $datetime->format('Y-m-d'),
            'time' => $datetime->format('H:i:s'),
        ]);

        $task = app(UpdatePostTask::class);

        $newDatetime = fake()->dateTimeBetween('-2 months', 'now');

        $dto = PostUpdateDto::from([
            'title' => 'New title',
            'content' => 'New content',
            'date' => $newDatetime->format('Y-m-d'),
            'time' => $newDatetime->format('H:i:s'),
        ]);

        $updatedPost = $task->run($post, $dto);

        $this->assertInstanceOf(Post::class, $updatedPost);
        $this->assertEquals('New title', $updatedPost->title);
        $this->assertEquals('New content', $updatedPost->content);
        $this->assertDatabaseHas('lifelog_posts', ['id' => $post->id, 'title' => 'New title']);
    }
}
