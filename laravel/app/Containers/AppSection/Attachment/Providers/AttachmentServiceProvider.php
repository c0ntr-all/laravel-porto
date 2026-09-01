<?php declare(strict_types=1);

namespace App\Containers\AppSection\Attachment\Providers;

use App\Containers\AppSection\Attachment\Services\FileableUploaderRegistry;
use App\Containers\AppSection\Attachment\Uploaders\DocumentFileableUploader;
use App\Containers\AppSection\Attachment\Uploaders\ImageFileableUploader;
use App\Containers\AppSection\Attachment\Uploaders\VideoFileableUploader;
use Illuminate\Support\ServiceProvider;

class AttachmentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->tag([
            ImageFileableUploader::class,
            VideoFileableUploader::class,
            DocumentFileableUploader::class,
        ], 'attachment.fileable-uploaders');

        $this->app->singleton(FileableUploaderRegistry::class, function ($app) {
            return new FileableUploaderRegistry($app->tagged('attachment.fileable-uploaders'));
        });
    }
}
