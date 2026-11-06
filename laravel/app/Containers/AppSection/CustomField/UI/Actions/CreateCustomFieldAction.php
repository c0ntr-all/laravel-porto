<?php declare(strict_types=1);

namespace App\Containers\AppSection\CustomField\UI\Actions;

use App\Containers\AppSection\CustomField\Data\DTO\CustomFieldCreateData;
use App\Containers\AppSection\CustomField\Models\CustomField;
use App\Containers\AppSection\CustomField\Tasks\CreateCustomFieldTask;
use App\Containers\AppSection\CustomField\UI\API\Requests\CreateCustomFieldRequest;
use App\Containers\AppSection\CustomField\UI\API\Transformers\CustomFieldTransformer;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Enums\EventTypesEnum;
use App\Ship\Exceptions\CreateResourceFailedException;
use App\Ship\Parents\Actions\UseCaseAction;
use Illuminate\Http\JsonResponse;

class CreateCustomFieldAction extends UseCaseAction
{
    protected ?ContainerAliasEnum $containerAliasEnum = ContainerAliasEnum::CUSTOM_FIELD;
    protected ?EventTypesEnum $eventTypesEnum = EventTypesEnum::CREATED;
    public function __construct(
        private readonly CreateCustomFieldTask $createCustomFieldTask,
    ) {
        parent::__construct();
    }

    /**
     * @throws CreateResourceFailedException
     */
    public function handle(CustomFieldCreateData $dto): CustomField
    {
        $customField = $this->createCustomFieldTask->run($dto);

        $this->recordUseCase($customField);

        return $customField;
    }

    public function asController(CreateCustomFieldRequest $request): JsonResponse
    {
        $dto = CustomFieldCreateData::from([
            ...$request->validated(),
            'user_id' => (int) auth()->id(),
        ]);
        $customField = $this->handle($dto);

        return fractal($customField, new CustomFieldTransformer())
            ->withResourceName(ContainerAliasEnum::CUSTOM_FIELD->value)
            ->addMeta(['message' => 'Custom field created successfully!'])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
