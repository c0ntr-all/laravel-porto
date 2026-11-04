<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Image\Tests\Functional;

use App\Containers\AppSection\User\Models\User;
use App\Containers\GallerySection\Album\Models\Album;
use App\Containers\GallerySection\Image\Jobs\GenerateGalleryImageThumbJob;
use App\Containers\GallerySection\Image\Models\Image;
use App\Ship\Enums\FileSourceEnum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ComputerLibraryTest extends TestCase
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

        $this->libraryRoot = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'gallery-library-' . uniqid('', true);
        mkdir($this->libraryRoot . DIRECTORY_SEPARATOR . 'Images' . DIRECTORY_SEPARATOR . 'Vacation', 0777, true);

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

    public function test_user_can_list_one_folder_level_without_reading_pixels(): void
    {
        $this->writePng($this->libraryRoot . '/Images/a.jpg');
        $this->writePng($this->libraryRoot . '/Images/b.png');
        file_put_contents($this->libraryRoot . '/Images/notes.txt', 'skip');
        file_put_contents($this->libraryRoot . '/Images/raw.cr2', 'skip');
        $this->writePng($this->libraryRoot . '/Images/Vacation/nested.jpg');

        $response = $this->actingAs($this->user, 'api')
            ->getJson('/api/v1/gallery/library/folders?path=F:\\Images&per_page=1&page=2');

        $response->assertOk()
            ->assertJsonPath('data.path', 'F:\\Images')
            ->assertJsonPath('data.folders.0.name', 'Vacation')
            ->assertJsonPath('data.files.0.name', 'b.png')
            ->assertJsonPath('data.files.0.in_gallery', false)
            ->assertJsonPath('meta.total', 2)
            ->assertJsonPath('meta.page', 2)
            ->assertJsonPath('meta.last_page', 2);

        $this->assertNotSame(0, $response->json('data.files.0.size'));
        $this->assertNotSame(0, $response->json('data.files.0.mtime'));
        $this->assertSame('Images', $response->json('data.breadcrumbs.0.name'));
    }

    public function test_windows_import_registers_a_file_once_and_builds_only_list_thumb(): void
    {
        $absolute = $this->libraryRoot . '/Images/windows-photo.png';
        $this->writePng($absolute);
        $other = Album::create([
            'user_id' => $this->user->id,
            'name' => 'Trip',
        ]);

        $first = $this->actingAs($this->user, 'api')
            ->postJson("/api/v1/gallery/albums/{$this->album->id}/images/upload-windows", [
                'paths' => ['F:\\Images\\windows-photo.png'],
            ]);

        $first->assertOk()
            ->assertJsonPath('data.0.attributes.thumb_status', 'pending');

        $imageId = (int) $first->json('data.0.id');
        $image = Image::query()->findOrFail($imageId);
        $this->assertNotNull($image->file_size);
        $this->assertNotNull($image->file_mtime);
        $this->assertSame('png', $image->extension);

        GenerateGalleryImageThumbJob::dispatchSync($imageId);
        $image->refresh();

        $disk = Storage::disk((string) config('image.disk', 'public'));
        $this->assertSame('ready', $image->thumb_status);
        $this->assertTrue($disk->exists($image->relativePath('list_thumb')));
        $this->assertFalse($disk->exists($image->relativePath('preview_thumb')));

        $second = $this->actingAs($this->user, 'api')
            ->postJson("/api/v1/gallery/albums/{$other->id}/images/upload-windows", [
                'paths' => ['F:\\Images\\windows-photo.png'],
            ]);

        $second->assertOk()->assertJsonPath('data.0.id', (string) $imageId);
        $this->assertSame(1, Image::query()->where('external_url', 'F:\\Images\\windows-photo.png')->count());
        $this->assertTrue($other->images()->where('gallery_images.id', $imageId)->exists());
        $this->assertTrue($this->album->images()->where('gallery_images.id', $imageId)->exists());

        $listed = $this->actingAs($this->user, 'api')
            ->getJson('/api/v1/gallery/library/folders?path=F:\\Images');
        $listed->assertOk()->assertJsonPath('data.files.0.in_gallery', true);
    }

    public function test_missing_windows_file_marks_the_image_and_keeps_the_album_readable(): void
    {
        $image = Image::create([
            'user_id' => $this->user->id,
            'album_id' => $this->album->id,
            'source' => FileSourceEnum::WINDOWS->value,
            'extension' => 'png',
            'external_url' => 'F:\\Images\\gone.png',
            'width' => 10,
            'height' => 10,
        ]);

        $this->actingAs($this->user, 'api')
            ->get("/api/v1/gallery/images/{$image->id}/file")
            ->assertNotFound();

        $this->assertTrue($image->refresh()->file_missing);

        $this->actingAs($this->user, 'api')
            ->getJson("/api/v1/gallery/albums/{$this->album->id}/images")
            ->assertOk()
            ->assertJsonPath('meta.count', 1)
            ->assertJsonPath('data.0.attributes.file_missing', true);
    }

    public function test_user_can_relink_a_renamed_file_by_size_and_mtime(): void
    {
        $absolute = $this->libraryRoot . '/Images/renamed.png';
        $this->writePng($absolute);

        $image = Image::create([
            'user_id' => $this->user->id,
            'album_id' => $this->album->id,
            'source' => FileSourceEnum::WINDOWS->value,
            'extension' => 'png',
            'external_url' => 'F:\\Images\\original.png',
            'width' => 20,
            'height' => 10,
            'file_size' => filesize($absolute),
            'file_mtime' => filemtime($absolute),
            'file_missing' => true,
        ]);

        $this->actingAs($this->user, 'api')
            ->postJson('/api/v1/gallery/library/relink', [
                'path' => 'F:\\Images',
            ])
            ->assertOk()
            ->assertJsonPath('data.relinked', 1);

        $image->refresh();
        $this->assertSame('F:\\Images\\renamed.png', $image->external_url);
        $this->assertFalse($image->file_missing);
    }

    public function test_browse_thumbnail_is_queued_until_the_cache_exists(): void
    {
        $absolute = $this->libraryRoot . '/Images/a.jpg';
        $this->writePng($absolute);

        $response = $this->actingAs($this->user, 'api')
            ->postJson('/api/v1/gallery/library/thumbnails', [
                'paths' => ['F:\\Images\\a.jpg'],
            ]);

        $response->assertOk();
        $this->assertContains($response->json('data.0.status'), ['pending', 'ready']);
    }

    private function writePng(string $path): void
    {
        $image = imagecreatetruecolor(20, 10);
        ob_start();
        imagepng($image);
        $binary = ob_get_clean();
        imagedestroy($image);
        file_put_contents($path, $binary ?: '');
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
