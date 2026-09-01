<?php declare(strict_types=1);

namespace App\Ship\Tests\Unit;

use App\Containers\AppSection\Attachment\Services\FileableUploaderRegistry;
use App\Containers\AppSection\Attachment\Uploaders\DocumentFileableUploader;
use App\Containers\AppSection\Attachment\Uploaders\ImageFileableUploader;
use App\Containers\AppSection\Attachment\Uploaders\VideoFileableUploader;
use App\Containers\AppSection\Document\Enums\DocumentMimeEnum;
use PHPUnit\Framework\TestCase;

class FileableUploaderRegistryTest extends TestCase
{
    public function test_resolves_image_uploader_for_image_mime(): void
    {
        $registry = new FileableUploaderRegistry([
            new ImageFileableUploader(
                $this->createStub(\App\Containers\GallerySection\Album\Tasks\GetSystemAlbumTask::class),
                $this->createStub(\App\Containers\GallerySection\Image\UI\Actions\UploadImageFromDeviceAction::class),
            ),
            new VideoFileableUploader(
                $this->createStub(\App\Containers\GallerySection\Album\Tasks\GetSystemAlbumTask::class),
                $this->createStub(\App\Containers\GallerySection\Video\UI\Actions\UploadVideoFromDeviceAction::class),
            ),
            new DocumentFileableUploader(
                $this->createStub(\App\Containers\AppSection\Document\Tasks\CreateDocumentFromUploadTask::class),
            ),
        ]);

        $file = \Illuminate\Http\UploadedFile::fake()->image('photo.jpg');

        $this->assertInstanceOf(ImageFileableUploader::class, $registry->resolve($file));
    }

    public function test_resolves_document_uploader_for_pdf_mime(): void
    {
        $registry = new FileableUploaderRegistry([
            new ImageFileableUploader(
                $this->createStub(\App\Containers\GallerySection\Album\Tasks\GetSystemAlbumTask::class),
                $this->createStub(\App\Containers\GallerySection\Image\UI\Actions\UploadImageFromDeviceAction::class),
            ),
            new VideoFileableUploader(
                $this->createStub(\App\Containers\GallerySection\Album\Tasks\GetSystemAlbumTask::class),
                $this->createStub(\App\Containers\GallerySection\Video\UI\Actions\UploadVideoFromDeviceAction::class),
            ),
            new DocumentFileableUploader(
                $this->createStub(\App\Containers\AppSection\Document\Tasks\CreateDocumentFromUploadTask::class),
            ),
        ]);

        $file = \Illuminate\Http\UploadedFile::fake()->create('report.pdf', 100, DocumentMimeEnum::PDF->value);

        $this->assertInstanceOf(DocumentFileableUploader::class, $registry->resolve($file));
    }
}
