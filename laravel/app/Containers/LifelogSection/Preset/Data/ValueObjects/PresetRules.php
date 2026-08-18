<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Preset\Data\ValueObjects;

use App\Ship\Parents\ValueObjects\ValueObject;

final class PresetRules extends ValueObject
{
    /**
     * @param string[] $tags
     */
    public function __construct(
        public readonly array $tags = [],
        public readonly ?string $dateFrom = null,
        public readonly ?string $dateTo = null,
        public readonly ?string $text = null,
    )
    {
    }

    public static function fromArray(array $data): static
    {
        return new self(
            tags: array_values($data['tags'] ?? []),
            dateFrom: $data['date_from'] ?? null,
            dateTo: $data['date_to'] ?? null,
            text: $data['text'] ?? null,
        );
    }

    public function toArray(): array
    {
        $data = [
            'tags' => $this->tags,
            'date_from' => $this->dateFrom,
            'date_to' => $this->dateTo,
            'text' => $this->text,
        ];

        return array_filter(
            $data,
            static fn (mixed $value): bool => $value !== null && $value !== [] && $value !== ''
        );
    }
}
