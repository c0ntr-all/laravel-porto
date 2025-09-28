<?php declare(strict_types=1);

namespace App\Containers\AppSection\Tag\Observers;

use App\Containers\AppSection\Tag\Models\Tag;
use Illuminate\Support\Str;

class TagObserver
{
    public function creating(Tag $tag): void
    {
        $this->setSlug($tag);
    }

    public function updating(Tag $tag): void
    {
        $this->setSlug($tag);
    }

    protected function setSlug(Tag $tag): void
    {
        $tag->slug = Str::slug($tag->name);
    }
}
