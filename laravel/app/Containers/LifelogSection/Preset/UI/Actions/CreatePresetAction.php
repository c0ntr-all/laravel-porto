<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Preset\UI\Actions;

use App\Containers\AppSection\ActivityLog\Tasks\CreateActivityUseCaseTask;
use App\Containers\LifelogSection\Preset\Data\DTO\PresetCreateDto;
use App\Containers\LifelogSection\Preset\Models\Preset;
use App\Containers\LifelogSection\Preset\Tasks\CreatePresetTask;
use App\Containers\LifelogSection\Preset\UI\API\Requests\CreateRequest;
use App\Containers\LifelogSection\Preset\UI\API\Transformers\PresetTransformer;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Enums\EventTypesEnum;
use App\Ship\Parents\Actions\UseCaseAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class CreatePresetAction extends UseCaseAction
{
    protected ?ContainerAliasEnum $containerAliasEnum = ContainerAliasEnum::LL_PRESET;
    protected ?EventTypesEnum $eventTypesEnum = EventTypesEnum::CREATED;

    public function __construct(
        private readonly CreatePresetTask $createPresetTask,
        private readonly CreateActivityUseCaseTask $createActivityUseCaseTask
    )
    {
        parent::__construct();
    }

    public function handle(PresetCreateDto $dto): Preset
    {
        $preset = $this->createPresetTask->run($dto);

        DB::afterCommit(function () use ($preset) {
            $this->createActivityUseCaseTask->run($preset, $this->eventTypesEnum->value);
        });

        return $preset;
    }

    /**
     * @throws \Exception
     */
    public function asController(CreateRequest $request): JsonResponse
    {
        $dto = PresetCreateDto::from([
            ...$request->validated(),
            'user_id' => auth()->id(),
        ]);

        $preset = $this->handle($dto);

        return fractal($preset, new PresetTransformer())
            ->withResourceName(ContainerAliasEnum::LL_PRESET->value)
            ->addMeta(['message' => 'New preset successfully created!'])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
