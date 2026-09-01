<?php declare(strict_types=1);

namespace App\Containers\AppSection\Document\Models;

use App\Containers\AppSection\Attachment\Models\Traits\HasFileableAttachments;
use App\Containers\AppSection\User\Models\Traits\HasUser;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Models\ActivityLoggableModel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Support\Facades\Storage;

class Document extends ActivityLoggableModel
{
    use HasUuids,
        HasUser,
        HasFileableAttachments;

    protected ContainerAliasEnum $loggableType = ContainerAliasEnum::APP_DOCUMENT;

    protected $table = 'app_documents';

    protected $fillable = [
        'id',
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
