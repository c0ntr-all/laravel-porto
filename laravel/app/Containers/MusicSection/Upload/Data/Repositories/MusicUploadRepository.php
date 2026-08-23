<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Upload\Data\Repositories;

use App\Containers\MusicSection\Upload\Data\DTO\CreateUploadDto;
use App\Containers\MusicSection\Upload\Enums\UploadStatusEnum;
use App\Containers\MusicSection\Upload\Enums\UploadTrackStatusEnum;
use App\Containers\MusicSection\Upload\Models\MusicUpload;
use App\Containers\MusicSection\Upload\Models\MusicUploadTrack;
use App\Ship\Parents\QueryBuilder\QueryBuilder;
use Illuminate\Pagination\CursorPaginator;
use Spatie\QueryBuilder\AllowedFilter;

class MusicUploadRepository
{
    public function getWithCursor(): CursorPaginator
    {
        return QueryBuilder::for(MusicUpload::class)
                           ->allowedFilters([
                               AllowedFilter::exact('status'),
                               AllowedFilter::exact('artist_id'),
                               AllowedFilter::partial('artist_name'),
                           ])
                           ->allowedSorts(['created_at', 'started_at', 'finished_at'])
                           ->allowedIncludes(['artist', 'tracks'])
                           ->with(['artist'])
                           ->orderByDesc('created_at')
                           ->cursorPaginate(20);
    }

    public function getTracksWithCursor(MusicUpload $upload): CursorPaginator
    {
        return QueryBuilder::for($upload->tracks())
                           ->allowedFilters([
                               AllowedFilter::exact('status'),
                               AllowedFilter::partial('album_name'),
                               AllowedFilter::partial('track_name'),
                           ])
                           ->allowedSorts(['created_at', 'album_name', 'track_name'])
                           ->orderBy('id')
                           ->cursorPaginate(50);
    }

    public function create(CreateUploadDto $dto): MusicUpload
    {
        return MusicUpload::create([
            'user_id' => $dto->user_id,
            'source_path' => $dto->path,
            'status' => UploadStatusEnum::Pending,
        ]);
    }

    public function markRunning(MusicUpload $upload): MusicUpload
    {
        $upload->update([
            'status' => UploadStatusEnum::Running,
            'started_at' => now(),
            'error_message' => null,
        ]);

        return $upload->refresh();
    }

    public function finalize(MusicUpload $upload, array $payload): MusicUpload
    {
        $upload->update($payload);

        return $upload->refresh();
    }

    public function addTrackLog(
        MusicUpload $upload,
        UploadTrackStatusEnum $status,
        string $sourcePath,
        ?int $trackId = null,
        ?string $albumName = null,
        ?string $trackName = null,
        ?array $snapshot = null,
        ?string $errorMessage = null,
    ): MusicUploadTrack {
        return $upload->tracks()->create([
            'track_id' => $trackId,
            'album_name' => $albumName,
            'track_name' => $trackName,
            'source_path' => $sourcePath,
            'status' => $status,
            'snapshot' => $snapshot,
            'error_message' => $errorMessage,
        ]);
    }
}
