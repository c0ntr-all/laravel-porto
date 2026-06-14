<?php declare(strict_types=1);

namespace App\Containers\AppSection\Authentication\UI\Actions;

use App\Containers\AppSection\Authentication\Data\DTO\CreatePasswordGrantTokenDto;
use App\Containers\AppSection\Authentication\Tasks\CreatePasswordGrantTokenTask;
use App\Containers\AppSection\Authentication\UI\API\Requests\LoginRequest;
use Illuminate\Http\Client\Response;
use App\Ship\Parents\Actions\BaseAction;

class LoginAction extends BaseAction
{

    public function __construct(
        private readonly CreatePasswordGrantTokenTask $createPasswordGrantTokenTask
    )
    {
    }

    public function handle(CreatePasswordGrantTokenDto $dto): Response
    {
        return $this->createPasswordGrantTokenTask->run($dto);
    }

    public function asController(LoginRequest $request)
    {
        $dto = CreatePasswordGrantTokenDto::from($request->validated());

        $response = $this->handle($dto);

        if ($response->successful()) {
            return $response->json();
        }

        if ($response->status() === 400) {
            $decoded = json_decode($response->body(), true);

            return response()->json($decoded, 400);
        }

        return response()->json(
            ['meta' => [
                'message' => 'Authentication failed'
            ]],
            401
        );
    }
}
