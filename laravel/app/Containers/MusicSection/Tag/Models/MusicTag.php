<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Tag\Models;

use App\Containers\MusicSection\Track\Models\Track;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MusicTag extends Model
{
    protected $table = 'music_tags';

    protected $fillable = [
        'user_id',
        'parent_id',
        'group_id',
        'name',
        'slug',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(MusicTagGroup::class, 'group_id');
    }

    public function tracks(): BelongsToMany
    {
        return $this->belongsToMany(Track::class, 'music_track_tag', 'tag_id', 'track_id')
                    ->using(MusicTrackTag::class)
                    ->withTimestamps();
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(__CLASS__, 'parent_id');
    }

    public function tags(): HasMany
    {
        return $this->hasMany(__CLASS__, 'parent_id')->with('tags');
    }
}
