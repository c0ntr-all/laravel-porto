<?php declare(strict_types=1);

namespace App\Containers\AppSection\User\UI\Transformer;

use App\Containers\AppSection\User\Models\User;
use League\Fractal\TransformerAbstract;

class UserTransformer extends TransformerAbstract
{
    public function transform(User $user): array
    {
        $roles = [];

        try {
            $roles = $user->getRoleNames()->values()->all();
        } catch (\Throwable) {
            $roles = [];
        }

        return [
            'id' => (string) $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $roles[0] ?? 'user',
            'roles' => $roles,
            'avatar' => $user->getAvatarUrl(),
            'created_at' => $user->created_at?->toIso8601String(),
        ];
    }
}
