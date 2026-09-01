<?php declare(strict_types=1);

namespace App\Containers\DashboardSection\Widget\Data\ValueObjects;

use App\Containers\DashboardSection\Widget\Enums\WidgetViewEnum;
use App\Ship\Parents\ValueObjects\ValueObject;

final class WidgetPayload extends ValueObject
{
    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $meta
     */
    public function __construct(
        public readonly string $type,
        public readonly string $title,
        public readonly WidgetViewEnum $view,
        public readonly array $data = [],
        public readonly ?string $html = null,
        public readonly array $meta = [],
        public readonly bool $ok = true,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $meta
     */
    public static function make(
        string $type,
        string $title,
        WidgetViewEnum $view,
        array $data = [],
        ?string $html = null,
        array $meta = [],
    ): self {
        return new self($type, $title, $view, $data, $html, $meta, true);
    }

    public static function error(string $type, string $title, string $message): self
    {
        return new self(
            type: $type,
            title: $title,
            view: WidgetViewEnum::TEXT,
            data: ['content' => $message],
            meta: ['error' => true],
            ok: false,
        );
    }

    public static function fromArray(array $data): static
    {
        return new self(
            type: (string) ($data['type'] ?? ''),
            title: (string) ($data['title'] ?? ''),
            view: WidgetViewEnum::from((string) ($data['view'] ?? WidgetViewEnum::LIST->value)),
            data: is_array($data['data'] ?? null) ? $data['data'] : [],
            html: isset($data['html']) ? (string) $data['html'] : null,
            meta: is_array($data['meta'] ?? null) ? $data['meta'] : [],
            ok: (bool) ($data['ok'] ?? true),
        );
    }

    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'title' => $this->title,
            'view' => $this->view->value,
            'data' => $this->data,
            'html' => $this->html,
            'meta' => $this->meta,
            'ok' => $this->ok,
        ];
    }
}
