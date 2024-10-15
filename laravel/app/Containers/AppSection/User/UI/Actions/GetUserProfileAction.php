<?php declare(strict_types=1);

namespace App\Containers\AppSection\User\UI\Actions;

use App\Containers\AppSection\User\UI\API\Resources\ProfileResource;
use Illuminate\Contracts\Auth\Authenticatable;
use Lorisleiva\Actions\Concerns\AsAction;

class GetUserProfileAction
{
    use AsAction;

    public function handle(): ?Authenticatable
    {
        return auth()->user();
    }

    public function asController()
    {
        return new ProfileResource($this->handle());
    }
}
