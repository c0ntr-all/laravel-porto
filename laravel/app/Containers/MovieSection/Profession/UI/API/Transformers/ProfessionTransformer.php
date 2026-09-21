<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Profession\UI\API\Transformers;

use App\Containers\MovieSection\Profession\Models\Profession;
use League\Fractal\TransformerAbstract;

class ProfessionTransformer extends TransformerAbstract
{
    public function transform(Profession $profession): array
    {
        return [
            'id' => $profession->id,
            'en_name' => $profession->en_name,
            'name' => $profession->name,
            'created_at' => $profession->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $profession->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
