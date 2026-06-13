<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Period\Events;

use App\Containers\LifelogSection\Period\Models\Period;
use Illuminate\Queue\SerializesModels;

abstract class PeriodEvent
{
    use SerializesModels;

    protected string $eventType = 'unknown';

    public function __construct(
        protected Period $period
    )
    {
    }

    public function getPeriod(): Period
    {
        return $this->period;
    }

    public function getEventType(): string
    {
        return $this->eventType;
    }
}
