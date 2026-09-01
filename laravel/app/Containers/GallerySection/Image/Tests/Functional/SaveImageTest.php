<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Image\Tests\Functional;

use App\Containers\AppSection\User\Models\User;
use App\Containers\GallerySection\Album\Enums\SystemAlbumsEnum;
use App\Containers\GallerySection\Album\Models\Album;
use App\Containers\GallerySection\Image\Models\Image;
use App\Ship\Enums\FileSourceEnum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SaveImageTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Album $album;
    private Album $saveAlbum;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->album = Album::create([
            'user_id' => $this->user->id,
            'name' => 'Photos',
        ]);
        $this->saveAlbum = Album::withoutGlobalScopes()->firstOrCreate(
            ['system_code' => SystemAlbumsEnum::SAVE->value],
            ['name' => 'Save', 'user_id' => null],
        );
    }

    public function test_user_can_save_image_into_system_save_album_idempotently(): void
    {
        $disk = Storage::disk((string) config('image.disk', 'public'));
        $image = Image::create([
            'user_id' => $this->user->id,
            'album_id' => $this->album->id,
            'source' => FileSourceEnum::DEVICE->value,
            'extension' => 'png',
            'width' => 10,
            'height' => 10,
        ]);
        $disk->put($image->relativePath('base'), 'original-bytes');
        $disk->put($image->relativePath('list_thumb'), 'thumb-bytes');

        $first = $this->actingAs($this->user, 'api')
            ->postJson("/api/v1/gallery/images/{$image->id}/save");

        $first->assertCreated()
            ->assertJsonPath('data.type', 'gallery_images')
            ->assertJsonPath('data.attributes.album_id', (string) $this->saveAlbum->id);

        $savedId = $first->json('data.id');
        $this->assertNotSame($image->id, $savedId);
        $this->assertDatabaseHas('gallery_images', [
            'id' => $savedId,
            'album_id' => $this->saveAlbum->id,
            'user_id' => $this->user->id,
            'saved_from_id' => $image->id,
        ]);

        $saved = Image::query()->findOrFail($savedId);
        $this->assertTrue($disk->exists($saved->relativePath('base')));
        $this->assertSame('original-bytes', $disk->get($saved->relativePath('base')));

        $second = $this->actingAs($this->user, 'api')
            ->postJson("/api/v1/gallery/images/{$image->id}/save");

        $second->assertOk()
            ->assertJsonPath('data.id', $savedId);
        $this->assertSame(1, Image::query()->where('saved_from_id', $image->id)->count());
    }
}
