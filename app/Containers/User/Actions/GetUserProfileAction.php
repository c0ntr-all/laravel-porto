<?php

namespace App\Containers\User\Actions;

use Lorisleiva\Actions\Concerns\AsAction;

class GetUserProfileAction
{
    use AsAction;

    public function handle()
    {
        return ['test'];
    }

    public function asController()
    {
        return $this->handle();
    }
}
