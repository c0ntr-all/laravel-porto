<?php declare(strict_types=1);

namespace App\Containers\AppSection\CustomField\UI\Actions;

use App\Containers\AppSection\CustomField\Models\CustomField;
use App\Containers\AppSection\CustomField\Tasks\DeleteCustomFieldTask;
use App\Containers\AppSection\CustomField\UI\API\Requests\DeleteCustomFieldRequest;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Enums\EventTypesEnum;
use App\Ship\Parents\Actions\UseCaseAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DeleteCustomFieldAction extends UseCaseAction
{
    protected ?ContainerAliasEnum $containerAliasEnum = ContainerAliasEnum::CUSTOM_FIELD;
    protected ?EventTypesEnum $eventTypesEnum = EventTypesEnum::DELETED;

    public function __construct(
        private readonly DeleteCustomFieldTask $deleteCustomFieldTask,
    ) {
        parent::__construct();
    }

    public function handle(CustomField $customField): bool
    {
        return DB::transaction(function () use ($customField): bool {
            $this->recordUseCase($customField);

            return $this->deleteCustomFieldTask->run($customField);
        });
    }

    public function asController(CustomField $customField, DeleteCustomFieldRequest $request): JsonResponse
    {
        $this->handle($customField);

        return response()->json([
            'meta' => [
                'message' => 'Custom field successfully deleted!',
            ],
        ]);
    }
}
