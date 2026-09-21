<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Profession\UI\Actions;

use App\Containers\MovieSection\Profession\Models\Profession;
use App\Containers\MovieSection\Profession\UI\API\Requests\GetRequest;
use App\Containers\MovieSection\Profession\UI\API\Transformers\ProfessionTransformer;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;

class GetProfessionAction extends BaseAction
{
    public function handle(Profession $profession): Profession
    {
        return $profession;
    }

    public function asController(Profession $profession, GetRequest $request): JsonResponse
    {
        $profession = $this->handle($profession);

        return fractal($profession, new ProfessionTransformer())
            ->withResourceName(ContainerAliasEnum::MOVIE_PROFESSION->value)
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
