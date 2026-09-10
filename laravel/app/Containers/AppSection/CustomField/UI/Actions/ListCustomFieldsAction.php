<?php declare(strict_types=1);

namespace App\Containers\AppSection\CustomField\UI\Actions;

use App\Containers\AppSection\CustomField\Data\DTO\CustomFieldListDto;
use App\Containers\AppSection\CustomField\Tasks\ListCustomFieldsTask;
use App\Containers\AppSection\CustomField\UI\API\Requests\ListCustomFieldsRequest;
use App\Containers\AppSection\CustomField\UI\API\Transformers\CustomFieldTransformer;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;

class ListCustomFieldsAction extends BaseAction
{
    public function __construct(
        private readonly ListCustomFieldsTask $listCustomFieldsTask,
    ) {
    }

    public function handle(CustomFieldListDto $dto): Collection
    {
        return $this->listCustomFieldsTask->run($dto);
    }

    public function asController(ListCustomFieldsRequest $request): JsonResponse
    {
        $dto = CustomFieldListDto::from($request->validated());
        $dto->user_id = (int) auth()->id();

        $customFields = $this->handle($dto);

        return fractal($customFields, new CustomFieldTransformer())
            ->withResourceName(ContainerAliasEnum::CUSTOM_FIELD->value)
            ->addMeta(['count' => $customFields->count()])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
