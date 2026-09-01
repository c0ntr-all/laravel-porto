<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Video\Tests\Functional;

use App\Containers\AppSection\User\Models\User;
use App\Containers\GallerySection\Album\Models\Album;
use App\Containers\GallerySection\Video\Models\Video;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Enums\FileSourceEnum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GalleryVideoCommentsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_comment_on_gallery_video(): void
    {
        $user = User::factory()->create();
        $album = Album::create([
            'user_id' => $user->id,
            'name' => 'Clips',
        ]);
        $video = Video::create([
            'user_id' => $user->id,
            'album_id' => $album->id,
            'source' => FileSourceEnum::WEB->value,
            'extension' => 'mp4',
            'external_url' => 'https://cdn.example.com/a.mp4',
            'width' => 10,
            'height' => 10,
        ]);

        $response = $this->actingAs($user, 'api')
            ->postJson('/api/v1/comments', [
                'commentable_id' => $video->id,
                'commentable_type' => ContainerAliasEnum::GALLERY_VIDEO->value,
                'content' => 'Cool clip',
            ]);

        $response->assertOk()
            ->assertJsonPath('data.attributes.commentable_type', ContainerAliasEnum::GALLERY_VIDEO->value);

        $this->assertDatabaseHas('comments', [
            'commentable_id' => $video->id,
            'commentable_type' => ContainerAliasEnum::GALLERY_VIDEO->value,
            'content' => 'Cool clip',
        ]);
    }
}
