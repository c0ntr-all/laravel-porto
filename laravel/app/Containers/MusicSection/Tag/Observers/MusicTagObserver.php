<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Tag\Observers;

use App\Containers\MusicSection\Tag\Models\MusicTag;
use Illuminate\Support\Str;

class MusicTagObserver
{
    public function creating(MusicTag $tag): void
    {
        $this->setSlug($tag);
    }

    public function updating(MusicTag $tag): void
    {
        $this->setSlug($tag);
    }

    protected function setSlug(MusicTag $tag): void
    {
        $tag->slug = Str::slug($tag->name);
    }
}
