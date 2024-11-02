<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Media\Rules;

use App\Containers\GallerySection\Media\Enums\MediaImageMimeEnum;
use App\Containers\GallerySection\Media\Enums\MediaVideoMimeEnum;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidateMediaExtension implements ValidationRule
{
    public function validate(string $attribute, mixed $value, \Closure $fail): void
    {
        $allowedExtensions = array_merge(
            MediaImageMimeEnum::toArray(),
            MediaVideoMimeEnum::toArray()
        );

        $extension = pathinfo($value, PATHINFO_EXTENSION);

        if (!in_array(strtolower($extension), $allowedExtensions)) {
            $fail("The {$attribute} has an invalid file extension. Allowed extensions are: " . implode(', ', $allowedExtensions));
        }
    }
}
