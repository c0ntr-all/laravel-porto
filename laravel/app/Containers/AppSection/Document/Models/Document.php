<?php declare(strict_types=1);

namespace App\Containers\AppSection\Document\Models;

use App\Containers\AppSection\Attachment\Models\Traits\HasFileableAttachments;
use App\Containers\AppSection\User\Models\Traits\HasUser;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Models\ActivityLoggableModel;
use App\Ship\Models\Traits\HasUuidV7;
use Illuminate\Support\Facades\Storage;

class Document extends ActivityLoggableModel
{
    use HasUuidV7,
        HasUser,
        HasFileableAttachments;

    protected ContainerAliasEnum $loggableType = ContainerAliasEnum::APP_DOCUMENT;

    protected $table = 'app_documents';

    protected $fillable = [
        'user_id',
        'original_name',
        'mime_type',
        'extension',
        'size',
        'disk',
        'path',
    ];

    public function getDownloadUrlAttribute(): string
    {
        return Storage::disk($this->disk)->url($this->path);
    }
}
