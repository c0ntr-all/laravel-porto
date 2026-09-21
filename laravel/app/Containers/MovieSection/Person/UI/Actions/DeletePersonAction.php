<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Person\UI\Actions;

use App\Containers\MovieSection\Person\Models\Person;
use App\Containers\MovieSection\Person\Tasks\DeletePersonTask;
use App\Containers\MovieSection\Person\UI\API\Requests\DeleteRequest;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;

class DeletePersonAction extends BaseAction
{
    public function __construct(
        private readonly DeletePersonTask $deletePersonTask,
    ) {
    }

    public function handle(Person $person): bool
    {
        return $this->deletePersonTask->run($person);
    }

    public function asController(Person $person, DeleteRequest $request): JsonResponse
    {
        $this->handle($person);

        return response()->json([
            'meta' => [
                'message' => 'Person successfully deleted!',
            ],
        ]);
    }
}
