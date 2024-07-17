<?php

namespace App\Containers\User\UI\Actions;

use App\Containers\User\UI\API\Resources\ProfileResource;
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
