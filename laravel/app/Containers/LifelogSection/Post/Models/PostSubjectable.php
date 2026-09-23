<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Post\Models;

use Illuminate\Database\Eloquent\Relations\MorphPivot;

class PostSubjectable extends MorphPivot
{
    protected $table = 'lifelog_post_subjectables';

    public $incrementing = true;

    protected $casts = [
        'payload' => 'array',
    ];
}
