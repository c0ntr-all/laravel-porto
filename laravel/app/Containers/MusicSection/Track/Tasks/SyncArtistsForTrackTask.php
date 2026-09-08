<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Track\Tasks;

use App\Containers\MusicSection\Track\Data\Repositories\TrackRepository;
use App\Containers\MusicSection\Track\Enums\TrackArtistRoleEnum;
use App\Containers\MusicSection\Track\Models\Track;
use App\Ship\Parents\Tasks\Task as ParentTask;

class SyncArtistsForTrackTask extends ParentTask
{
    public function __construct(
        private readonly TrackRepository $trackRepository
    )
    {
    }

    /**
     * @param list<int|string> $primaryArtistIds
     * @param list<int|string> $featuredArtistIds
     */
    public function run(Track $track, array $primaryArtistIds, array $featuredArtistIds = []): array
    {
        $payload = [];

        foreach ($this->uniqueIds($featuredArtistIds) as $artistId) {
            $payload[$artistId] = [
                'is_author' => false,
                'role' => TrackArtistRoleEnum::Featured->value,
            ];
        }

        foreach ($this->uniqueIds($primaryArtistIds) as $artistId) {
            $payload[$artistId] = [
                'is_author' => true,
                'role' => TrackArtistRoleEnum::Primary->value,
            ];
        }

        return $this->trackRepository->syncArtists($track, $payload);
    }

    /**
     * @param list<int|string> $ids
     * @return list<int>
     */
    private function uniqueIds(array $ids): array
    {
        $unique = [];

        foreach ($ids as $id) {
            $id = (int) $id;
            if ($id > 0) {
                $unique[$id] = $id;
            }
        }

        return array_values($unique);
    }
}
