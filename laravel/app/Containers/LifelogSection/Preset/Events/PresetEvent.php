<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Preset\Events;

use App\Containers\LifelogSection\Preset\Models\Preset;
use Illuminate\Queue\SerializesModels;

abstract class PresetEvent
{
    use SerializesModels;

    protected string $eventType = 'unknown';

    public function __construct(
        protected Preset $preset
    )
    {
    }

    public function getPreset(): Preset
    {
        return $this->preset;
    }

    public function getEventType(): string
    {
        return $this->eventType;
    }
}
