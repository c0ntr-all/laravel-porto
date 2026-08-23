<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Upload\Tasks;

use App\Containers\MusicSection\Upload\Data\Repositories\MusicUploadRepository;
use App\Containers\MusicSection\Upload\Models\MusicUpload;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Pagination\CursorPaginator;

class ListUploadTracksTask extends ParentTask
{
    public function __construct(
        private readonly MusicUploadRepository $musicUploadRepository
    ) {
    }

    public function run(MusicUpload $upload): CursorPaginator
    {
        return $this->musicUploadRepository->getTracksWithCursor($upload);
    }
}
