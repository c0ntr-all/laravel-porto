<?php declare(strict_types=1);

namespace App\Ship\Enums;

enum EventTypesEnum: string
{
    case CREATED = 'created';
    case UPDATED = 'updated';
    case DELETED = 'deleted';
    case ATTACHED = 'attached';
    case DETACHED = 'detached';

    public function getEventMessage(): string
    {
        return match($this) {
            self::CREATED => 'Создан',
            self::UPDATED => 'Изменён',
            self::DELETED => 'Удалён',
            self::ATTACHED => 'Привязан',
            self::DETACHED => 'Отвязан'
        };
    }
}
