<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Movie\UI\API\Transformers;

use App\Containers\MovieSection\Movie\Models\MoviePersonCredit;
use League\Fractal\TransformerAbstract;

class MovieCreditTransformer extends TransformerAbstract
{
    /**
     * @return array{
     *     id: int,
     *     description: string|null,
     *     person: array{id: int, name: string, en_name: string|null, photo: string|null}|null,
     *     profession: array{id: int, en_name: string, name: string|null}|null
     * }
     */
    public function transform(MoviePersonCredit $credit): array
    {
        return self::map($credit) ?? [
            'id' => $credit->id,
            'description' => $credit->description,
            'person' => null,
            'profession' => null,
        ];
    }

    /**
     * @return array{
     *     id: int,
     *     description: string|null,
     *     person: array{id: int, name: string, en_name: string|null, photo: string|null},
     *     profession: array{id: int, en_name: string, name: string|null}
     * }|null
     */
    public static function map(MoviePersonCredit $credit): ?array
    {
        if ($credit->person === null || $credit->profession === null) {
            return null;
        }

        return [
            'id' => $credit->id,
            'description' => $credit->description,
            'person' => [
                'id' => $credit->person->id,
                'name' => $credit->person->name,
                'en_name' => $credit->person->en_name,
                'photo' => $credit->person->photo,
            ],
            'profession' => [
                'id' => $credit->profession->id,
                'en_name' => $credit->profession->en_name,
                'name' => $credit->profession->name,
            ],
        ];
    }
}
