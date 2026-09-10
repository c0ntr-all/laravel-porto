<?php declare(strict_types=1);

namespace App\Containers\AppSection\CustomField\UI\Actions;

use App\Containers\AppSection\CustomField\Data\DTO\CustomFieldUpdateData;
use App\Containers\AppSection\CustomField\Models\CustomField;
use App\Containers\AppSection\CustomField\Tasks\UpdateCustomFieldTask;
use App\Containers\AppSection\CustomField\UI\API\Requests\UpdateCustomFieldRequest;
use App\Containers\AppSection\CustomField\UI\API\Transformers\CustomFieldTransformer;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;

class UpdateCustomFieldAction extends BaseAction
{
    public function __construct(
        private readonly UpdateCustomFieldTask $updateCustomFieldTask,
    ) {
    }

    public function handle(CustomField $customField, CustomFieldUpdateData $dto): CustomField
    {
        return $this->updateCustomFieldTask->run($customField, $dto);
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
