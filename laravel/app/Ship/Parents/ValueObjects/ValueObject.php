<?php declare(strict_types=1);

namespace App\Ship\Parents\ValueObjects;

use JsonSerializable;

abstract class ValueObject implements JsonSerializable
{
    abstract public static function fromArray(array $data): static;

    abstract public function toArray(): array;

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public function equals(self $other): bool
    {
        return $this->toArray() === $other->toArray();
    }
}
