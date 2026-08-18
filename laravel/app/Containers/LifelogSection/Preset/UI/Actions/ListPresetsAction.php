<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Preset\UI\Actions;

use App\Containers\LifelogSection\Preset\Data\DTO\PresetListDto;
use App\Containers\LifelogSection\Preset\Tasks\ListPresetsTask;
use App\Containers\LifelogSection\Preset\UI\API\Requests\ListRequest;
use App\Containers\LifelogSection\Preset\UI\API\Transformers\PresetTransformer;
use App\Ship\Enums\ContainerAliasEnum;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use App\Ship\Parents\Actions\BaseAction;

class ListPresetsAction extends BaseAction
{

    public function __construct(
        private readonly ListPresetsTask $listPresetsTask
    )
    {
    }

    public function handle(PresetListDto $dto): Collection
    {
        return $this->listPresetsTask->run($dto);
    }

    public function asController(ListRequest $request): JsonResponse
    {
        $dto = PresetListDto::from($request->validated());
        $dto->user_id = auth()->id();

        $presets = $this->handle($dto);

        return fractal($presets, new PresetTransformer())
            ->parseIncludes(['tags'])
            ->withResourceName(ContainerAliasEnum::LL_PRESET->value)
            ->addMeta(['count' => $presets->count()])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
