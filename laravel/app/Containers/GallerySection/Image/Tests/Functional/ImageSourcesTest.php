<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Image\Tests\Functional;

use App\Containers\AppSection\User\Models\User;
use App\Containers\GallerySection\Album\Models\Album;
use App\Containers\GallerySection\Image\Models\Image;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Enums\EventTypesEnum;
use App\Ship\Enums\FileSourceEnum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImageSourcesTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Album $album;
    private string $libraryRoot;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->album = Album::create([
            'user_id' => $this->user->id,
            'name' => 'Photos',
        ]);

        $this->libraryRoot = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'gallery-images-' . uniqid('', true);
        mkdir($this->libraryRoot . DIRECTORY_SEPARATOR . 'Images', 0777, true);

        config([
            'filesystems.disks.windows_f.root' => $this->libraryRoot,
            'image.windows.disk' => 'windows_f',
            'image.windows.root_folder' => 'F:\\Images\\',
            'app.windows_images_root_folder' => 'F:\\Images\\',
        ]);
        Storage::forgetDisk('windows_f');
    }

    protected function tearDown(): void
    {
        $this->deleteDirectory($this->libraryRoot);
        parent::tearDown();
    }

    public function test_user_can_upload_image_from_device(): void
    {
        $file = $this->makeUploadedPng('from-device.png');

        $response = $this->actingAs($this->user, 'api')
            ->post("/api/v1/gallery/albums/{$this->album->id}/images/upload", [
                'file' => $file,
            ]);

        $response->assertOk()
            ->assertJsonPath('data.type', 'gallery_images')
            ->assertJsonPath('data.attributes.source', FileSourceEnum::DEVICE->value);

        $this->assertDatabaseHas('gallery_images', [
            'album_id' => $this->album->id,
            'user_id' => $this->user->id,
            'source' => FileSourceEnum::DEVICE->value,
        ]);

        $imageId = $response->json('data.id');

        $this->assertDatabaseHas('activity_use_case_logs', [
            'loggable_type' => ContainerAliasEnum::GALLERY_IMAGE->value,
            'loggable_id' => $imageId,
            'event_type' => EventTypesEnum::CREATED->value,
        ]);

        $relativePath = "userfiles/{$this->user->id}/images/{$this->album->id}/{$imageId}.png";
        $this->assertTrue(
            Storage::disk((string) config('image.disk', 'public'))->exists($relativePath),
            "Uploaded image must be stored under the same UUID as the database row: {$relativePath}"
        );
    }

    public function test_user_can_upload_image_from_web(): void
    {
        $png = $this->pngBinary();
        Http::fake([
            'https://cdn.example.com/photo.png' => Http::response($png, 200, ['Content-Type' => 'image/png']),
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->postJson("/api/v1/gallery/albums/{$this->album->id}/images/upload-web", [
                'link' => 'https://cdn.example.com/photo.png',
            ]);

        $response->assertOk()
            ->assertJsonPath('data.type', 'gallery_images')
            ->assertJsonPath('data.attributes.source', FileSourceEnum::WEB->value)
            ->assertJsonPath('data.attributes.original_path', 'https://cdn.example.com/photo.png');
    }

    public function test_user_can_upload_image_from_web_url_with_query_string(): void
    {
        $png = $this->pngBinary();
        $url = 'https://sun9-50.vkuserphoto.ru/s/v1/ig2/photo.jpg?quality=95&as=32x19,48x28&from=bu&cs=825x0';

        Http::fake([
            $url => Http::response($png, 200, ['Content-Type' => 'image/jpeg']),
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->postJson("/api/v1/gallery/albums/{$this->album->id}/images/upload-web", [
                'link' => $url,
            ]);

        $response->assertOk()
            ->assertJsonPath('data.type', 'gallery_images')
            ->assertJsonPath('data.attributes.source', FileSourceEnum::WEB->value)
            ->assertJsonPath('data.attributes.original_path', $url);

        $this->assertDatabaseHas('gallery_images', [
            'id' => $response->json('data.id'),
            'album_id' => $this->album->id,
            'extension' => 'jpg',
            'external_url' => $url,
            'source' => FileSourceEnum::WEB->value,
        ]);
    }

    public function test_user_can_register_windows_image_and_stream_it(): void
    {
        $relative = 'Images' . DIRECTORY_SEPARATOR . 'windows-photo.png';
        $absolute = $this->libraryRoot . DIRECTORY_SEPARATOR . $relative;
        $this->writePng($absolute);

        $response = $this->actingAs($this->user, 'api')
            ->postJson("/api/v1/gallery/albums/{$this->album->id}/images/upload-windows", [
                'paths' => ['F:\\Images\\windows-photo.png'],
            ]);

        $response->assertOk()
            ->assertJsonPath('data.0.type', 'gallery_images')
            ->assertJsonPath('data.0.attributes.source', FileSourceEnum::WINDOWS->value);

        $imageId = $response->json('data.0.id');
        $this->assertStringContainsString("/api/v1/gallery/images/{$imageId}/file", $response->json('data.0.attributes.original_path'));

        $this->assertDatabaseHas('gallery_images', [
            'id' => $imageId,
            'external_url' => 'F:\\Images\\windows-photo.png',
            'source' => FileSourceEnum::WINDOWS->value,
        ]);

        $fileResponse = $this->actingAs($this->user, 'api')
            ->get("/api/v1/gallery/images/{$imageId}/file");

        $fileResponse->assertOk();
        $this->assertSame('image/png', $fileResponse->headers->get('Content-Type'));
        $this->assertSame(file_get_contents($absolute), $fileResponse->streamedContent());
    }

    public function test_user_can_list_update_and_delete_image(): void
    {
        $image = Image::create([
            'user_id' => $this->user->id,
            'album_id' => $this->album->id,
            'source' => FileSourceEnum::WEB->value,
            'extension' => 'png',
            'external_url' => 'https://cdn.example.com/a.png',
            'width' => 10,
            'height' => 10,
        ]);

        $this->actingAs($this->user, 'api')
            ->getJson("/api/v1/gallery/albums/{$this->album->id}/images")
            ->assertOk()
            ->assertJsonPath('meta.count', 1)
            ->assertJsonPath('data.0.type', 'gallery_images');

        $this->actingAs($this->user, 'api')
            ->patchJson("/api/v1/gallery/images/{$image->id}", [
                'description' => 'Caption',
            ])
            ->assertOk()
            ->assertJsonPath('data.attributes.description', 'Caption');

        $this->actingAs($this->user, 'api')
            ->deleteJson("/api/v1/gallery/images/{$image->id}")
            ->assertOk();

        $this->assertDatabaseMissing('gallery_images', ['id' => $image->id]);
    }

    public function test_guest_cannot_stream_image_file(): void
    {
        $image = Image::create([
            'user_id' => $this->user->id,
            'album_id' => $this->album->id,
            'source' => FileSourceEnum::WINDOWS->value,
            'extension' => 'png',
            'external_url' => 'F:\\Images\\windows-photo.png',
            'width' => 10,
            'height' => 10,
        ]);

        $this->get("/api/v1/gallery/images/{$image->id}/file")
            ->assertUnauthorized();
    }

    private function makeUploadedPng(string $name): UploadedFile
    {
        $path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('png-', true) . '.png';
        $this->writePng($path);

        return new UploadedFile($path, $name, 'image/png', null, true);
    }

    private function writePng(string $path): void
    {
        file_put_contents($path, $this->pngBinary());
    }

    private function pngBinary(): string
    {
        $image = imagecreatetruecolor(20, 10);
        ob_start();
        imagepng($image);
        $binary = ob_get_clean();
        imagedestroy($image);

        return $binary ?: '';
    }

    private function deleteDirectory(string $directory): void
    {
        if (!is_dir($directory)) {
            return;
        }

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST,
        );

        foreach ($files as $file) {
            $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname());
        }

        rmdir($directory);
    }
}
