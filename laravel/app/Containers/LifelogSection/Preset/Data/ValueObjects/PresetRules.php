<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Preset\Data\ValueObjects;

use App\Containers\LifelogSection\Post\Enums\PostContentTypeEnum;
use App\Ship\Parents\ValueObjects\ValueObject;
use InvalidArgumentException;

final class PresetRules extends ValueObject
{
    /**
     * @param list<string> $tags
     * @param list<string> $contentTypes
     */
    public function __construct(
        public readonly array $tags = [],
        public readonly ?string $dateFrom = null,
        public readonly ?string $dateTo = null,
        public readonly ?string $text = null,
        public readonly array $contentTypes = [],
    ) {
        foreach ($this->contentTypes as $contentType) {
            if (PostContentTypeEnum::tryFrom($contentType) === null) {
                throw new InvalidArgumentException("Invalid content_type: {$contentType}");
            }
        }
    }

    public static function fromArray(array $data): static
    {
        return new self(
            tags: array_values($data['tags'] ?? []),
            dateFrom: $data['date_from'] ?? null,
            dateTo: $data['date_to'] ?? null,
            text: $data['text'] ?? null,
            contentTypes: self::normalizeContentTypes($data['content_type'] ?? []),
        );
    }

    public function toArray(): array
    {
        $data = [
            'tags' => $this->tags,
            'date_from' => $this->dateFrom,
            'date_to' => $this->dateTo,
            'text' => $this->text,
            'content_type' => $this->contentTypes,
        ];

        return array_filter(
            $data,
            static fn (mixed $value): bool => $value !== null && $value !== [] && $value !== ''
        );
    }

    public function matchesContentType(?PostContentTypeEnum $contentType): bool
    {
        if ($this->contentTypes === []) {
            return true;
        }

        if ($contentType === null) {
            return false;
        }

        return in_array($contentType->value, $this->contentTypes, true);
    }

    /**
     * @return list<string>
     */
    private static function normalizeContentTypes(mixed $value): array
    {
        if ($value === null || $value === '' || $value === []) {
            return [];
        }

        if (is_string($value)) {
            return [$value];
        }

        if (!is_array($value)) {
            throw new InvalidArgumentException('content_type must be a string or an array of strings.');
        }

        return array_values(array_unique(array_map(
            static fn (mixed $item): string => (string) $item,
            $value
        )));
    }
}
