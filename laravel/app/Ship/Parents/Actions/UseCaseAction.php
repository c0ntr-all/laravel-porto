<?php declare(strict_types=1);

namespace App\Ship\Parents\Actions;

use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Enums\EventTypesEnum;
use App\Ship\Events\UseCaseCompleted;
use App\Ship\Exceptions\ActionNotReadyException;
use App\Ship\Helpers\Correlation;
use App\Ship\Models\Interfaces\DBLoggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class UseCaseAction extends BaseAction
{
    protected ?ContainerAliasEnum $containerAliasEnum = null;
    protected ?EventTypesEnum $eventTypesEnum = null;

    /** * @throws ActionNotReadyException */
    public function __construct()
    {
        Correlation::init();

        if (!$this->containerAliasEnum) {
            throw new ActionNotReadyException('containerAliasEnum is not specified.');
        }
        if (!$this->eventTypesEnum) {
            throw new ActionNotReadyException('eventTypesEnum is not specified.');
        }
        $useCaseName = Correlation::makeUseCaseName($this->containerAliasEnum->value, $this->eventTypesEnum->value);
        Correlation::setUseCase($useCaseName);
    }

    /**
     * Records a user-facing use-case log after the current transaction commits.
     * System logs for nested domain events share the same correlation UUID.
     */
    protected function recordUseCase(DBLoggable&Model $model): void
    {
        $userId = (int) ($model->getAttribute('user_id') ?? auth()->id() ?? 0);

        if ($userId <= 0) {
            return;
        }

        $payload = new UseCaseCompleted(
            userId: $userId,
            loggableType: $model->getLoggableType(),
            loggableId: (string) $model->getKey(),
            eventType: $this->eventTypesEnum->value,
        );

        DB::afterCommit(static function () use ($payload): void {
            event($payload);
        });
    }
}
