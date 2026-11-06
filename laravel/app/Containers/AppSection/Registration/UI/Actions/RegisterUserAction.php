<?php declare(strict_types=1);

namespace App\Containers\AppSection\Registration\UI\Actions;

use App\Containers\AppSection\Authentication\Data\DTO\CreatePasswordGrantTokenDto;
use App\Containers\AppSection\Authentication\Tasks\CreatePasswordGrantTokenTask;
use App\Containers\AppSection\Registration\UI\API\Requests\RegisterRequest;
use App\Containers\AppSection\User\Data\DTO\CreateUserDto;
use App\Containers\AppSection\User\Models\User;
use App\Containers\AppSection\User\Tasks\CreateUserTask;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Support\Facades\DB;

class RegisterUserAction extends BaseAction
{

    public function __construct(
        private readonly CreateUserTask $createUserTask,
        private readonly CreatePasswordGrantTokenTask $createPasswordGrantTokenTask
    )
    {
    }

    public function handle(CreateUserDto $dto): User
    {
        return $this->createUserTask->run($dto);
    }

    public function asController(RegisterRequest $request)
    {
        $validated = $request->validated();
        unset($validated['password_confirm']);

        $user = DB::transaction(fn () => $this->handle(CreateUserDto::from($validated)));
        $response = $this->createPasswordGrantTokenTask->run(CreatePasswordGrantTokenDto::from($validated));

        if ($response->successful()) {
            return $response->json();
        }

        $user->delete();

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
