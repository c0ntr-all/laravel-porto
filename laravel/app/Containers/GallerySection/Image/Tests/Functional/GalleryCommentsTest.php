<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Image\Tests\Functional;

use App\Containers\AppSection\User\Models\User;
use App\Containers\GallerySection\Album\Models\Album;
use App\Containers\GallerySection\Image\Models\Image;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Enums\FileSourceEnum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GalleryCommentsTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Image $image;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $album = Album::create([
            'user_id' => $this->user->id,
            'name' => 'Photos',
        ]);
        $this->image = Image::create([
            'user_id' => $this->user->id,
            'album_id' => $album->id,
            'source' => FileSourceEnum::WEB->value,
            'extension' => 'png',
            'external_url' => 'https://cdn.example.com/a.png',
            'width' => 10,
            'height' => 10,
        ]);
    }

    public function test_user_can_comment_on_gallery_image_and_list_comments(): void
    {
        $create = $this->actingAs($this->user, 'api')
            ->postJson('/api/v1/comments', [
                'commentable_id' => $this->image->id,
                'commentable_type' => 'images',
                'content' => 'Nice shot',
            ]);

        $create->assertOk()
            ->assertJsonPath('data.type', 'comments')
            ->assertJsonPath('data.attributes.commentable_id', $this->image->id)
            ->assertJsonPath('data.attributes.commentable_type', ContainerAliasEnum::GALLERY_IMAGE->value);

        $this->assertDatabaseHas('comments', [
            'commentable_id' => $this->image->id,
            'commentable_type' => ContainerAliasEnum::GALLERY_IMAGE->value,
            'content' => 'Nice shot',
            'user_id' => $this->user->id,
        ]);

        $list = $this->actingAs($this->user, 'api')
            ->getJson('/api/v1/comments?' . http_build_query([
                'filter' => [
                    'commentable_id' => $this->image->id,
                    'commentable_type' => 'gallery_images',
                ],
            ]));

        $list->assertOk()
            ->assertJsonPath('meta.count', 1)
            ->assertJsonPath('data.0.attributes.content', 'Nice shot');

        $included = $this->actingAs($this->user, 'api')
            ->getJson("/api/v1/gallery/images/{$this->image->id}?include=comments");

        $included->assertOk()
            ->assertJsonPath('included.0.type', 'comments')
            ->assertJsonPath('included.0.attributes.content', 'Nice shot');
    }
}
