<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Upload\Tasks;

use App\Containers\MusicSection\Upload\Data\Repositories\MusicUploadRepository;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Pagination\CursorPaginator;

class ListUploadsTask extends ParentTask
{
    public function __construct(
        private readonly MusicUploadRepository $musicUploadRepository
    ) {
    }

    public function run(): CursorPaginator
    {
        return $this->musicUploadRepository->getWithCursor();
    }
}
