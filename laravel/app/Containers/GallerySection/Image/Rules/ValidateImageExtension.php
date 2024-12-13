<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Image\Rules;

use App\Containers\GallerySection\Image\Enums\ImageMimeEnum;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidateImageExtension implements ValidationRule
{
    public function validate(string $attribute, mixed $value, \Closure $fail): void
    {
        $allowedExtensions = ImageMimeEnum::toArray();

        $extension = pathinfo($value, PATHINFO_EXTENSION);

        if (!in_array(strtolower($extension), $allowedExtensions)) {
            $fail("The {$attribute} has an invalid file extension. Allowed extensions are: " . implode(', ', $allowedExtensions));
        }
    }
}
