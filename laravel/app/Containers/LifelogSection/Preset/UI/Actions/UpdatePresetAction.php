<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Preset\UI\Actions;

use App\Containers\AppSection\ActivityLog\Tasks\CreateActivityUseCaseTask;
use App\Containers\LifelogSection\Preset\Data\DTO\PresetUpdateDto;
use App\Containers\LifelogSection\Preset\Data\ValueObjects\PresetRules;
use App\Containers\LifelogSection\Preset\Models\Preset;
use App\Containers\LifelogSection\Preset\Tasks\UpdatePresetTask;
use App\Containers\LifelogSection\Preset\UI\API\Requests\UpdateRequest;
use App\Containers\LifelogSection\Preset\UI\API\Transformers\PresetTransformer;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Enums\EventTypesEnum;
use App\Ship\Parents\Actions\UseCaseAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class UpdatePresetAction extends UseCaseAction
{
    protected ?ContainerAliasEnum $containerAliasEnum = ContainerAliasEnum::LL_PRESET;
    protected ?EventTypesEnum $eventTypesEnum = EventTypesEnum::UPDATED;

    public function __construct(
        private readonly UpdatePresetTask $updatePresetTask,
        private readonly CreateActivityUseCaseTask $createActivityUseCaseTask
    )
    {
        parent::__construct();
    }

    public function handle(Preset $preset, PresetUpdateDto $dto): Preset
    {
        $preset = $this->updatePresetTask->run($preset, $dto);

        DB::afterCommit(function () use ($preset) {
            $this->createActivityUseCaseTask->run($preset, $this->eventTypesEnum->value);
        });

        return $preset;
    }

    public function asController(Preset $preset, UpdateRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $payload = [
            'user_id' => auth()->id(),
        ];

        foreach (['title', 'description', 'color', 'icon'] as $field) {
            if (array_key_exists($field, $validated)) {
                $payload[$field] = $validated[$field];
            }
        }

        if ($this->hasRulesUpdate($validated)) {
            $existingRules = $preset->rules?->toArray() ?? [];

            $payload['rules'] = PresetRules::fromArray([
                'tags' => array_key_exists('tags', $validated)
                    ? $validated['tags']
                    : ($existingRules['tags'] ?? []),
                'date_from' => array_key_exists('date_from', $validated)
                    ? $validated['date_from']
                    : ($existingRules['date_from'] ?? null),
                'date_to' => array_key_exists('date_to', $validated)
                    ? $validated['date_to']
                    : ($existingRules['date_to'] ?? null),
                'text' => array_key_exists('text', $validated)
                    ? $validated['text']
                    : ($existingRules['text'] ?? null),
            ]);
        }

        $preset = $this->handle($preset, PresetUpdateDto::from($payload));

        return fractal($preset, new PresetTransformer())
            ->parseIncludes(['tags'])
            ->withResourceName(ContainerAliasEnum::LL_PRESET->value)
            ->addMeta(['message' => 'Preset successfully updated!'])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }

    private function hasRulesUpdate(array $validated): bool
    {
        return collect(['tags', 'date_from', 'date_to', 'text'])
            ->contains(fn (string $field): bool => array_key_exists($field, $validated));
    }
}
