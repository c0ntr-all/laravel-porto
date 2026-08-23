<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Upload\Support;

use getID3;
use getid3_lib;

class Id3Reader
{
    public function read(string $absolutePath): array
    {
        $getID3 = new getID3();
        $getID3->encoding = 'UTF-8';
        $info = $getID3->analyze($absolutePath);
        getid3_lib::CopyTagsToComments($info);

        return $info;
    }
}
