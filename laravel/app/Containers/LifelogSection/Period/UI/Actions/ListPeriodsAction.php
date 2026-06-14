<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Period\UI\Actions;

use App\Containers\LifelogSection\Period\Data\DTO\PeriodListDto;
use App\Containers\LifelogSection\Period\Tasks\ListPeriodsTask;
use App\Containers\LifelogSection\Period\UI\API\Requests\ListPeriodsRequest;
use App\Containers\LifelogSection\Period\UI\API\Transformers\PeriodTransformer;
use App\Ship\Enums\ContainerAliasEnum;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use App\Ship\Parents\Actions\BaseAction;

class ListPeriodsAction extends BaseAction
{

    public function __construct(
        private readonly ListPeriodsTask $listPeriodsTask
    )
    {
    }

    public function handle(PeriodListDto $dto): Collection
    {
        return $this->listPeriodsTask->run($dto);
    }

    public function asController(ListPeriodsRequest $request): JsonResponse
    {
        $dto = PeriodListDto::from($request->validated());
        $dto->user_id = auth()->id();

        $periods = $this->handle($dto);

        return fractal($periods, new PeriodTransformer())
            ->withResourceName(ContainerAliasEnum::LL_PERIOD->value)
            ->addMeta(['count' => $periods->count()])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
