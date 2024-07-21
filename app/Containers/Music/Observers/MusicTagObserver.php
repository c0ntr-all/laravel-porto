<?php

namespace App\Observers;

use App\Models\Music\MusicTag;

class MusicTagObserver
{
    public function creating(MusicTag $tag): void
    {
        $this->setSlug($tag);
    }

    public function updating(MusicTag $tag): void
    {
        $tag->slug = \Str::slug($tag->name);
    }

    protected function setSlug(MusicTag $tag): void
    {
        $tag->slug = \Str::slug($tag->name);
    }
}
