<?php declare(strict_types=1);

namespace App\Containers\AppSection\CustomField\UI\Actions;

use App\Containers\AppSection\CustomField\Data\DTO\CustomFieldUpdateData;
use App\Containers\AppSection\CustomField\Models\CustomField;
use App\Containers\AppSection\CustomField\Tasks\UpdateCustomFieldTask;
use App\Containers\AppSection\CustomField\UI\API\Requests\UpdateCustomFieldRequest;
use App\Containers\AppSection\CustomField\UI\API\Transformers\CustomFieldTransformer;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Enums\EventTypesEnum;
use App\Ship\Parents\Actions\UseCaseAction;
use Illuminate\Http\JsonResponse;

class UpdateCustomFieldAction extends UseCaseAction
{
    protected ?ContainerAliasEnum $containerAliasEnum = ContainerAliasEnum::CUSTOM_FIELD;
    protected ?EventTypesEnum $eventTypesEnum = EventTypesEnum::UPDATED;
    public function __construct(
        private readonly UpdateCustomFieldTask $updateCustomFieldTask,
    ) {
        parent::__construct();
    }

    public function handle(CustomField $customField, CustomFieldUpdateData $dto): CustomField
    {
        $customField = $this->updateCustomFieldTask->run($customField, $dto);

        $this->recordUseCase($customField);

        return $customField;
    }

    public function asController(CustomField $customField, UpdateCustomFieldRequest $request): JsonResponse
    {
        $dto = CustomFieldUpdateData::from($request->validated());
        $customField = $this->handle($customField, $dto);

        return fractal($customField, new CustomFieldTransformer())
            ->withResourceName(ContainerAliasEnum::CUSTOM_FIELD->value)
            ->addMeta(['message' => 'Custom field successfully updated!'])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
