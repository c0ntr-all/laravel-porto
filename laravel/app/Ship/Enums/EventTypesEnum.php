<?php declare(strict_types=1);

namespace App\Ship\Enums;

enum EventTypesEnum: string
{
    case CREATED = 'created';
    case UPDATED = 'updated';
    case DELETED = 'deleted';
    case TAG_ADDED = 'tag_added';
    case TAG_REMOVED = 'tag_removed';
    case ATTACHMENT_ADDED = 'attachment_added';
    case ATTACHMENT_REMOVED = 'attachment_removed';

    public function getEventMessage(): string
    {
        return match($this) {
            self::CREATED => 'Создан',
            self::UPDATED => 'Изменён',
            self::DELETED => 'Удалён',
            self::TAG_ADDED => 'Добавлен тег',
            self::TAG_REMOVED => 'Удалён тег',
            self::ATTACHMENT_ADDED => 'Добавлено вложение',
            self::ATTACHMENT_REMOVED => 'Удалено вложение',
        };
    }
}
