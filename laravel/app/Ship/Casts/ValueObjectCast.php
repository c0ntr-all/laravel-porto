<?php declare(strict_types=1);

namespace App\Ship\Casts;

use App\Ship\Parents\ValueObjects\ValueObject;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use JsonException;

class ValueObjectCast implements CastsAttributes
{
    public function __construct(
        protected string $valueObjectClass
    )
    {
    }

    /**
     * @throws JsonException
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): ?ValueObject
    {
        if ($value === null) {
            return null;
        }

        $data = is_array($value)
            ? $value
            : json_decode($value, true, 512, JSON_THROW_ON_ERROR);

        return $this->valueObjectClass::fromArray($data ?? []);
    }

    /**
     * @throws JsonException
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_array($value)) {
            $value = $this->valueObjectClass::fromArray($value);
        }

        if (!$value instanceof ValueObject) {
            throw new InvalidArgumentException(sprintf(
                'The [%s] cast expects an instance of %s or an array.',
                static::class,
                $this->valueObjectClass
            ));
        }

        return json_encode($value->toArray(), JSON_THROW_ON_ERROR);
    }
}
