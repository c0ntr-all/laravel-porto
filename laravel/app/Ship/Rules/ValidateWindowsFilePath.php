<?php declare(strict_types=1);

namespace App\Ship\Rules;

use App\Ship\Helpers\WindowsPathHelper;
use Illuminate\Contracts\Validation\ValidationRule;
use InvalidArgumentException;

class ValidateWindowsFilePath implements ValidationRule
{
    /**
     * @param list<string> $allowedExtensions
     */
    public function __construct(
        private readonly string $rootFolder,
        private readonly string $disk,
        private readonly array $allowedExtensions,
    ) {
    }

    public function validate(string $attribute, mixed $value, \Closure $fail): void
    {
        if (!is_string($value) || $value === '') {
            $fail("The {$attribute} must be a Windows file path.");
            return;
        }

        try {
            $normalized = WindowsPathHelper::normalizeWindows($value);
        } catch (InvalidArgumentException $exception) {
            $fail($exception->getMessage());
            return;
        }

        if (!WindowsPathHelper::isUnderRoot($normalized, $this->rootFolder)) {
            $fail("The {$attribute} must be inside {$this->rootFolder}");
            return;
        }

        $extension = strtolower(pathinfo(str_replace('\\', '/', $normalized), PATHINFO_EXTENSION));
        if (!in_array($extension, $this->allowedExtensions, true)) {
            $fail(
                "The {$attribute} has an invalid file extension. Allowed extensions are: "
                . implode(', ', $this->allowedExtensions)
            );
            return;
        }

        try {
            WindowsPathHelper::assertReadableFile($normalized, $this->disk, $this->rootFolder);
        } catch (InvalidArgumentException) {
            $fail("The " . basename(str_replace('\\', '/', $normalized)) . " doesn't exist at {$normalized}");
        }
    }
}
