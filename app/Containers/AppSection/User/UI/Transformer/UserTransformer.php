<?php declare(strict_types=1);

namespace App\Containers\AppSection\User\UI\Transformer;

use App\Containers\AppSection\User\Models\User;
use League\Fractal\TransformerAbstract;

class UserTransformer extends TransformerAbstract
{
    public function transform(User $user): array
    {
        return [
            'name' => $user->name,
            'email' => $user->email,
        ];
    }
}
