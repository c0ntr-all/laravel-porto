<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Upload\Tasks;

use App\Ship\Parents\Tasks\Task as ParentTask;

class GetAlbumCoverTask extends ParentTask
{
    protected const string DEFAULT_COVER_NAME = 'Cover.jpg';

    /**
     * Returns the path to the cover in the folder, if there is one
     *
     * @param string $albumPath
     * @return string|null
     */
    public function run(string $albumPath): ?string
    {
        $defaultCover = $albumPath . DIRECTORY_SEPARATOR . static::DEFAULT_COVER_NAME;

        return file_exists($defaultCover) ? $defaultCover : null;
    }
}
