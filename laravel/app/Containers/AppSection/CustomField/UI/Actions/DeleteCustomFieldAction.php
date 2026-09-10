<?php declare(strict_types=1);

namespace App\Containers\AppSection\CustomField\UI\Actions;

use App\Containers\AppSection\CustomField\Models\CustomField;
use App\Containers\AppSection\CustomField\Tasks\DeleteCustomFieldTask;
use App\Containers\AppSection\CustomField\UI\API\Requests\DeleteCustomFieldRequest;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;

class DeleteCustomFieldAction extends BaseAction
{
    public function __construct(
        private readonly DeleteCustomFieldTask $deleteCustomFieldTask,
    ) {
    }

    public function handle(CustomField $customField): bool
    {
        return $this->deleteCustomFieldTask->run($customField);
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
