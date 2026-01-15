<?php declare(strict_types=1);

namespace App\Ship\Parents\Actions;

use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Enums\EventTypesEnum;
use App\Ship\Exceptions\ActionNotReadyException;
use App\Ship\Helpers\Correlation;
use Lorisleiva\Actions\Concerns\AsAction;

class UseCaseAction extends BaseAction
{
    protected ?ContainerAliasEnum $containerAliasEnum = null;
    protected ?EventTypesEnum $eventTypesEnum = null;

    /** * @throws ActionNotReadyException */
    public function __construct()
    {
        parent::__construct();

        if (!$this->containerAliasEnum) {
            throw new ActionNotReadyException('containerAliasEnum is not specified.');
        }
        if (!$this->eventTypesEnum) {
            throw new ActionNotReadyException('eventTypesEnum is not specified.');
        }
        $useCaseName = Correlation::makeUseCaseName($this->containerAliasEnum->value, $this->eventTypesEnum->value);
        Correlation::setUseCase($useCaseName);
    }
}
