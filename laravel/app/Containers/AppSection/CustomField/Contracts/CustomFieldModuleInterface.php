<?php declare(strict_types=1);

namespace App\Containers\AppSection\CustomField\Contracts;

use App\Containers\AppSection\CustomField\Enums\CustomFieldTypeEnum;

interface CustomFieldModuleInterface
{
    public function type(): CustomFieldTypeEnum;

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function validateAndNormalize(array $payload): array;
}
