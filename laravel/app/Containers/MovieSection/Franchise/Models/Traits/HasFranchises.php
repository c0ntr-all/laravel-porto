<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Franchise\Models\Traits;

use App\Containers\MovieSection\Franchise\Models\Franchise;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

trait HasFranchises
{
    protected static function bootHasFranchises(): void
    {
        static::deleting(fn ($item) => $item->franchises()->detach());
    }

    public function franchises(): BelongsToMany
    {
        return $this->belongsToMany(
            Franchise::class,
            'movie_franchise_movie',
            'movie_id',
            'franchise_id',
        )
            ->withPivot(['order'])
            ->withTimestamps();
    }
}
