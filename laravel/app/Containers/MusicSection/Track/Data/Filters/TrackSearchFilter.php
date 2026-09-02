<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Track\Data\Filters;

use App\Ship\Parents\QueryBuilder\Filter as FilterInterface;
use Illuminate\Database\Eloquent\Builder;

class TrackSearchFilter implements FilterInterface
{
    public const MIN_LENGTH = 3;

    public function __invoke(Builder $query, mixed $value, string $property): void
    {
        $term = $this->normalize($value);
        if ($term === null) {
            return;
        }

        if (!str_contains($term, '-')) {
            $this->constrainTrackName($query, $term);

            return;
        }

        [$artist, $track] = $this->splitArtistAndTrack($term);

        if ($artist !== '') {
            $this->constrainArtistName($query, $artist);
        }

        if ($track !== '') {
            $this->constrainTrackName($query, $track);
        }
    }

    private function normalize(mixed $value): ?string
    {
        if (is_array($value)) {
            $value = $value[0] ?? '';
        }

        if (!is_string($value) && !is_numeric($value)) {
            return null;
        }

        $value = trim((string) $value);

        if (mb_strlen($value) < self::MIN_LENGTH) {
            return null;
        }

        return $value;
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function splitArtistAndTrack(string $term): array
    {
        $parts = explode('-', $term, 2);

        return [
            trim($parts[0]),
            trim($parts[1] ?? ''),
        ];
    }

    private function constrainTrackName(Builder $query, string $term): void
    {
        $table = $query->getModel()->getTable();
        $pattern = $this->like($term);

        $query->where(function (Builder $inner) use ($table, $pattern): void {
            $inner->where($table.'.name', 'like', $pattern)
                ->orWhere($table.'.credits', 'like', $pattern);
        });
    }

    private function constrainArtistName(Builder $query, string $term): void
    {
        $pattern = $this->like($term);

        $query->whereHas('artists', function (Builder $artistQuery) use ($pattern) {
            $artistQuery->where('music_artists.name', 'like', $pattern);
        });
    }

    private function like(string $term): string
    {
        return '%' . addcslashes($term, '%_\\') . '%';
    }
}
