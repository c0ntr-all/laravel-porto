<?php declare(strict_types=1);

namespace App\Containers\AppSection\User\Models\Traits;

trait HasAvatar
{
    public function getAvatarUrl(): ?string
    {
        $path = $this->avatar;

        if (!is_string($path) || $path === '') {
            return null;
        }

        if (str_contains($path, 'http://') || str_contains($path, 'https://')) {
            return $path;
        }

        return url('') . '/storage/' . ltrim($path, '/');
    }
}
