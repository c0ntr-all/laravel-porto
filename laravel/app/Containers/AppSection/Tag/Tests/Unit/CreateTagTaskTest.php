<?php declare(strict_types=1);

namespace App\Containers\AppSection\Tag\Tests\Unit;

use App\Containers\AppSection\User\Models\User;
use App\Containers\AppSection\Tag\Data\DTO\TagCreateDto;
use App\Containers\AppSection\Tag\Models\Tag;
use App\Containers\AppSection\Tag\Tasks\CreateTagTask;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateTagTaskTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_a_tag_with_slug(): void
    {
        $user = User::factory()->create();
        $task = app(CreateTagTask::class);

        $dto = TagCreateDto::from([
            'user_id' => $user->id,
            'name' => 'My New Tag',
        ]);

        $result = $task->run($dto);

        $this->assertInstanceOf(Tag::class, $result);
        $this->assertEquals('My New Tag', $result->name);
        $this->assertEquals('my-new-tag', $result->slug);
        $this->assertDatabaseHas('tags', [
            'name' => 'My New Tag',
            'slug' => 'my-new-tag',
            'user_id' => $user->id,
        ]);
    }

    public function test_it_creates_a_tag_with_content(): void
    {
        $user = User::factory()->create();
        $task = app(CreateTagTask::class);

        $dto = TagCreateDto::from([
            'user_id' => $user->id,
            'name' => 'Tag With Content',
            'content' => 'This is tag content',
        ]);

        $result = $task->run($dto);

        $this->assertEquals('This is tag content', $result->content);
    }
}
