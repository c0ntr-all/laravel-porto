<?php declare(strict_types=1);

use App\Containers\AppSection\Attachment\UI\Actions\UploadAttachmentAction;
use Illuminate\Support\Facades\Route;

Route::post('app/attachments/upload', UploadAttachmentAction::class)
     ->middleware(['auth:api']);
