<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Upload\Tasks;

use App\Ship\Parents\Tasks\Task as ParentTask;
use Exception;

class GetTracksInfoTask extends ParentTask
{
    public function __construct(
        private readonly GetTrackInfoTask $getTrackInfoTask
    ){
    }

    /**
     * @throws Exception
     */
    public function run(array $tracksPaths): array
    {
        if (!empty($tracksPaths)) {
            $total = [];

            foreach($tracksPaths as $trackPath) {
                $total[] = $this->getTrackInfoTask->run($trackPath);
            }

            return $total;
        } else {
            throw new Exception('This folder doesn\'t contains any tracks!');
        }
    }
}
