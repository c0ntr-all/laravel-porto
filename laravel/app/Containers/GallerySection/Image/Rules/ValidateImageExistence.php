<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Image\Rules;

use Illuminate\Contracts\Validation\ValidationRule;

class ValidateImageExistence implements ValidationRule
{
    public function validate(string $attribute, mixed $value, \Closure $fail): void
    {
        if (!is_file($value)) {
            $filename = basename($value);
            $fail("The {$filename} doesn't exists!");
        }
    }
}
