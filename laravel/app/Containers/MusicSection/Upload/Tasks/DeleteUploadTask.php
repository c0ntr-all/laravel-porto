<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Upload\Tasks;

use App\Containers\MusicSection\Upload\Models\MusicUpload;
use App\Ship\Parents\Tasks\Task as ParentTask;

class DeleteUploadTask extends ParentTask
{
    public function run(MusicUpload $upload): ?bool
    {
        return $upload->delete();
    }
}
