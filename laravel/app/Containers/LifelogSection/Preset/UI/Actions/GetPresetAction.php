<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Preset\UI\Actions;

use App\Containers\LifelogSection\Preset\Models\Preset;
use App\Containers\LifelogSection\Preset\UI\API\Requests\GetRequest;
use App\Containers\LifelogSection\Preset\UI\API\Transformers\PresetTransformer;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;

class GetPresetAction extends BaseAction
{
    public function handle(Preset $preset): Preset
    {
        return $preset->load('tags');
    }

    public function asController(Preset $preset, GetRequest $request): JsonResponse
    {
        $preset = $this->handle($preset);

        return fractal($preset, new PresetTransformer())
            ->parseIncludes(['tags'])
            ->withResourceName(ContainerAliasEnum::LL_PRESET->value)
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
