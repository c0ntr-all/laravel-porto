<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Period\UI\Actions;

use App\Containers\AppSection\ActivityLog\Tasks\CreateActivityUseCaseTask;
use App\Containers\LifelogSection\Period\Data\DTO\PeriodCreateDto;
use App\Containers\LifelogSection\Period\Models\Period;
use App\Containers\LifelogSection\Period\Tasks\CreatePeriodTask;
use App\Containers\LifelogSection\Period\UI\API\Requests\CreateRequest;
use App\Containers\LifelogSection\Period\UI\API\Transformers\PeriodTransformer;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Enums\EventTypesEnum;
use App\Ship\Helpers\Correlation;
use App\Ship\Parents\Actions\UseCaseAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class CreatePeriodAction extends UseCaseAction
{
    protected ?ContainerAliasEnum $containerAliasEnum = ContainerAliasEnum::LL_PERIOD;
    protected ?EventTypesEnum $eventTypesEnum = EventTypesEnum::CREATED;

    public function __construct(
        private readonly CreatePeriodTask $createPeriodTask,
        private readonly CreateActivityUseCaseTask $createActivityUseCaseTask
    )
    {
        parent::__construct();
    }

    public function handle(PeriodCreateDto $dto): Period
    {
        $period = $this->createPeriodTask->run($dto);

        DB::afterCommit(function () use ($period) {
            $this->createActivityUseCaseTask->run($period, $this->eventTypesEnum->value);
        });

        return $period;
    }

    /**
     * @throws \Exception
     */
    public function asController(CreateRequest $request): JsonResponse
    {
        $dto = PeriodCreateDto::from([
            ...$request->validated(),
            'user_id' => auth()->id(),
        ]);

        $period = $this->handle($dto);

        return fractal($period, new PeriodTransformer())
            ->withResourceName('ll_periods')
            ->addMeta([
                'message' => 'New period successfully created!',
                'correlation_uuid' => Correlation::getUuid(),
            ])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
