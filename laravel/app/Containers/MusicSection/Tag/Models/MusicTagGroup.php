<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Tag\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class MusicTagGroup extends Model
{
    protected $table = 'music_tag_groups';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_system',
        'is_active',
        'display_order',
    ];

    protected function casts(): array
    {
        return [
            'is_system' => 'boolean',
            'is_active' => 'boolean',
            'display_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (MusicTagGroup $group): void {
            if ($group->slug) {
                return;
            }

            $base = Str::slug((string) $group->name);
            $group->slug = substr($base !== '' ? $base : 'group', 0, 50);
        });
    }

    public function tags(): HasMany
    {
        return $this->hasMany(MusicTag::class, 'group_id');
    }
}
