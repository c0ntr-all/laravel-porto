<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Person\UI\API\Transformers;

use App\Containers\AppSection\Country\Models\Country;
use App\Containers\MovieSection\Folder\Models\Folder;
use App\Containers\MovieSection\Genre\Models\Genre;
use App\Containers\MovieSection\Movie\Models\Movie;
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
     *         kp_id: int|null,
     *         title: string,
     *         short_description: string|null,
     *         year: int|null,
     *         type: string,
     *         cover: string|null,
     *         kp_img: string|null,
     *         kp_rating: float|null,
     *         genres: list<array{id: int, kp_id: int|null, name: string, slug: string}>,
     *         countries: list<array{id: int, name: string, description: string|null, image: string|null}>,
     *         folder_slugs: list<string>,
     *         folder_ids: list<int>
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
     *         kp_id: int|null,
     *         title: string,
     *         short_description: string|null,
     *         year: int|null,
     *         type: string,
     *         cover: string|null,
     *         kp_img: string|null,
     *         kp_rating: float|null,
     *         genres: list<array{id: int, kp_id: int|null, name: string, slug: string}>,
     *         countries: list<array{id: int, name: string, description: string|null, image: string|null}>,
     *         folder_slugs: list<string>,
     *         folder_ids: list<int>
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
                'kp_id' => $credit->movie->kp_id,
                'title' => $credit->movie->title,
                'short_description' => $credit->movie->short_description,
                'year' => $credit->movie->year,
                'type' => $credit->movie->type->value,
                'cover' => $credit->movie->cover,
                'kp_img' => $credit->movie->kp_img,
                'kp_rating' => $credit->movie->kp_rating !== null
                    ? (float) $credit->movie->kp_rating
                    : null,
                'genres' => self::mapGenres($credit->movie),
                'countries' => self::mapCountries($credit->movie),
                'folder_slugs' => self::mapFolderSlugs($credit->movie),
                'folder_ids' => self::mapFolderIds($credit->movie),
            ],
            'profession' => [
                'id' => $credit->profession->id,
                'en_name' => $credit->profession->en_name,
                'name' => $credit->profession->name,
            ],
        ];
    }

    /**
     * @return list<array{id: int, kp_id: int|null, name: string, slug: string}>
     */
    private static function mapGenres(Movie $movie): array
    {
        if (!$movie->relationLoaded('genres')) {
            return [];
        }

        return $movie->genres
            ->map(static fn (Genre $genre): array => [
                'id' => $genre->id,
                'kp_id' => $genre->kp_id,
                'name' => $genre->name,
                'slug' => $genre->slug,
            ])
            ->values()
            ->all();
    }

    /**
     * @return list<array{id: int, name: string, description: string|null, image: string|null}>
     */
    private static function mapCountries(Movie $movie): array
    {
        if (!$movie->relationLoaded('countries')) {
            return [];
        }

        return $movie->countries
            ->map(static fn (Country $country): array => [
                'id' => $country->id,
                'name' => $country->name,
                'description' => $country->description,
                'image' => $country->image,
            ])
            ->values()
            ->all();
    }

    /**
     * @return list<string>
     */
    private static function mapFolderSlugs(Movie $movie): array
    {
        if (!$movie->relationLoaded('folders')) {
            return [];
        }

        return $movie->folders
            ->pluck('slug')
            ->filter(static fn ($slug) => is_string($slug) && $slug !== '')
            ->values()
            ->all();
    }

    /**
     * @return list<int>
     */
    private static function mapFolderIds(Movie $movie): array
    {
        if (!$movie->relationLoaded('folders')) {
            return [];
        }

        return $movie->folders
            ->map(static fn (Folder $folder): int => (int) $folder->id)
            ->values()
            ->all();
    }
}
