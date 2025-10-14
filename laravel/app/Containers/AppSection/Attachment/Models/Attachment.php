<?php declare(strict_types=1);

namespace App\Containers\AppSection\Attachment\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Attachment extends Model
{
    use HasUuids;

    protected $guarded = [];

    public function attachable(): MorphTo
    {
        return $this->morphTo();
    }

    public function fileable(): MorphTo
    {
        return $this->morphTo();
    }
}
