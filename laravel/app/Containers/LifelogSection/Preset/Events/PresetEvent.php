<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Preset\Events;

use App\Containers\LifelogSection\Preset\Models\Preset;
use App\Ship\Events\DomainActivityEvent;
use Illuminate\Database\Eloquent\Model;

abstract class PresetEvent extends DomainActivityEvent
{
    public function __construct(
        protected Preset $preset
    ) {
    }

    public function getPreset(): Preset
    {
        return $this->preset;
    }

    public function activityMainType(): string
    {
        return $this->preset->getLoggableType();
    }

    public function activityMainId(): string
    {
        return (string) $this->preset->id;
    }

    public function activityMetadata(): array
    {
        $metadata = $this->snapshot(['title', 'color', 'start_date', 'end_date']);
        $metadata['rules'] = $this->preset->rules?->toArray();

        return $metadata;
    }

    protected function activitySubject(): Model
    {
        return $this->preset;
    }
}
