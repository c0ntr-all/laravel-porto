<?php declare(strict_types=1);

namespace App\Containers\AppSection\CustomField\Services;

use App\Containers\AppSection\CustomField\Contracts\CustomFieldModuleInterface;
use App\Containers\AppSection\CustomField\Enums\CustomFieldTypeEnum;
use App\Containers\AppSection\CustomField\Exceptions\UnsupportedCustomFieldTypeException;

class CustomFieldModuleRegistry
{
    /**
     * @var array<string, CustomFieldModuleInterface>
     */
    private array $modules = [];

    /**
     * @param iterable<CustomFieldModuleInterface> $modules
     */
    public function __construct(iterable $modules)
    {
        foreach ($modules as $module) {
            $this->modules[$module->type()->value] = $module;
        }
    }

    public function resolve(string|CustomFieldTypeEnum $type): CustomFieldModuleInterface
    {
        $key = $type instanceof CustomFieldTypeEnum ? $type->value : $type;

        if (!isset($this->modules[$key])) {
            throw new UnsupportedCustomFieldTypeException($key);
        }

        return $this->modules[$key];
    }
}
