<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Video\Contracts;

interface VideoSourceContract
{
    public function getDisk(): string;

    public function getRelativePath(): string;

    public function getExtension(): string;

    public function getOriginalName(): string;

    public function getStoredExternalUrl(): ?string;
}
