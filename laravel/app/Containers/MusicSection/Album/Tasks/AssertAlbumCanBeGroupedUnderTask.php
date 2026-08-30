<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Album\Tasks;

use App\Containers\MusicSection\Album\Models\Album;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Validation\ValidationException;

class AssertAlbumCanBeGroupedUnderTask extends ParentTask
{
    /**
     * @param list<int> $artistIds
     */
    public function run(?int $parentId, array $artistIds, ?Album $album = null): void
    {
        if ($parentId === null) {
            return;
        }

        if ($album !== null && (int) $album->id === $parentId) {
            throw ValidationException::withMessages([
                'parent_id' => 'An album cannot be a version of itself.',
            ]);
        }

        $parent = Album::query()->find($parentId);

        if ($parent === null) {
            throw ValidationException::withMessages([
                'parent_id' => 'The selected parent album does not exist.',
            ]);
        }

        if ($parent->parent_id !== null) {
            throw ValidationException::withMessages([
                'parent_id' => 'Only a main album can have versions. Nested versions are not allowed.',
            ]);
        }

        if ($album !== null && $album->versions()->exists()) {
            throw ValidationException::withMessages([
                'parent_id' => 'An album that already has versions cannot become a version of another album.',
            ]);
        }

        $parentArtistIds = $parent->artists()
            ->pluck('music_artists.id')
            ->map(static fn (mixed $id): int => (int) $id)
            ->all();

        $childArtistIds = array_values(array_unique(array_map('intval', $artistIds)));

        if ($parentArtistIds !== [] && $childArtistIds !== [] && array_intersect($childArtistIds, $parentArtistIds) === []) {
            throw ValidationException::withMessages([
                'parent_id' => 'The version must share at least one artist with the main album.',
            ]);
        }
    }
}
