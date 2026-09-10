<?php declare(strict_types=1);

namespace App\Containers\AppSection\CustomField\Exceptions;

use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class UnsupportedCustomFieldTypeException extends UnprocessableEntityHttpException
{
    public function __construct(string $type)
    {
        parent::__construct("Unsupported custom field type: {$type}");
    }
}
