<?php

namespace App\Ship\Models\Traits;

trait HasImage
{
    /**
     * Accessor for getting absolute path of model image
     *
     * @return string
     */
    public function getFullImageAttribute(): string
    {
        $rootPath = url('') . '/storage/';

        if (str_contains($this->image, 'http')) {
            return $this->image;
        } else {
            return !empty($this->image) ? $rootPath . $this->image : $rootPath . 'no-image.jpg';
        }
    }
}
