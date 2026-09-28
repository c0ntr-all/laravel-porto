<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Franchise\UI\API\Transformers;

use App\Containers\MovieSection\Franchise\Models\Franchise;
use League\Fractal\TransformerAbstract;

class FranchiseTransformer extends TransformerAbstract
{
    public function transform(Franchise $franchise): array
    {
        return [
            'id' => $franchise->id,
            'user_id' => $franchise->user_id,
            'name' => $franchise->name,
            'description' => $franchise->description,
            'order' => $franchise->order,
            'created_at' => $franchise->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $franchise->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
