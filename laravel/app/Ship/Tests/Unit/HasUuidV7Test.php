<?php declare(strict_types=1);

namespace App\Ship\Tests\Unit;

use App\Containers\AppSection\User\Models\User;
use App\Containers\GallerySection\Album\Models\Album;
use App\Containers\GallerySection\Image\Models\Image;
use App\Ship\Enums\FileSourceEnum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class HasUuidV7Test extends TestCase
{
    use RefreshDatabase;

    public function test_it_assigns_uuid_v7_on_create_and_keeps_integer_id(): void
    {
        $user = User::factory()->create();
        $album = Album::create([
            'user_id' => $user->id,
            'name' => 'Photos',
        ]);

        $image = Image::create([
            'user_id' => $user->id,
            'album_id' => $album->id,
            'source' => FileSourceEnum::WEB->value,
            'extension' => 'png',
            'external_url' => 'https://cdn.example.com/a.png',
            'width' => 10,
            'height' => 10,
        ]);

        $this->assertTrue(is_numeric($image->id));
        $this->assertTrue(Str::isUuid($image->uuid));
        $this->assertSame('7', $image->uuid[14]);
        $this->assertTrue(Str::isUuid($album->uuid));
        $this->assertEquals($image->id, Image::query()->whereIdOrUuid($image->uuid)->value('id'));
    }
}
