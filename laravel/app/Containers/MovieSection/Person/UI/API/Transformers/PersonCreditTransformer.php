<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Person\UI\API\Transformers;

use App\Containers\MovieSection\Movie\Models\MoviePersonCredit;
use League\Fractal\TransformerAbstract;

class PersonCreditTransformer extends TransformerAbstract
{
    /**
     * @return array{
     *     id: int,
     *     description: string|null,
     *     movie: array{
     *         id: int,
     *         title: string,
     *         year: int|null,
     *         type: string,
     *         cover: string|null,
     *         kp_rating: float|null
     *     }|null,
     *     profession: array{id: int, en_name: string, name: string|null}|null
     * }
     */
    public function transform(MoviePersonCredit $credit): array
    {
        return self::map($credit) ?? [
            'id' => $credit->id,
            'description' => $credit->description,
            'movie' => null,
            'profession' => null,
        ];
    }

    /**
     * @return array{
     *     id: int,
     *     description: string|null,
     *     movie: array{
     *         id: int,
     *         title: string,
     *         year: int|null,
     *         type: string,
     *         cover: string|null,
     *         kp_rating: float|null
     *     },
     *     profession: array{id: int, en_name: string, name: string|null}
     * }|null
     */
    public static function map(MoviePersonCredit $credit): ?array
    {
        if ($credit->movie === null || $credit->profession === null) {
            return null;
        }

        return [
            'id' => $credit->id,
            'description' => $credit->description,
            'movie' => [
                'id' => $credit->movie->id,
                'title' => $credit->movie->title,
                'year' => $credit->movie->year,
                'type' => $credit->movie->type->value,
                'cover' => $credit->movie->cover,
                'kp_rating' => $credit->movie->kp_rating !== null
                    ? (float) $credit->movie->kp_rating
                    : null,
            ],
            'profession' => [
                'id' => $credit->profession->id,
                'en_name' => $credit->profession->en_name,
                'name' => $credit->profession->name,
            ],
        ];
    }
}
