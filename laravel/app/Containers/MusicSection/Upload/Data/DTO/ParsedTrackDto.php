<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Upload\Data\DTO;

use App\Ship\Parents\DTO\Data;

class ParsedTrackDto extends Data
{
    public string $linux_path;
    public string $windows_path;
    public string $title;
    public string $album;
    public string $artist;
    public ?string $genre = null;
    public ?string $year = null;
    public ?string $date = null;
    public int $track_number = 0;
    public ?int $disc_number = null;
    public ?string $credits = null;
    /** @var list<string> */
    public array $featured_artists = [];
    public ?string $duration = null;
    public ?int $bitrate = null;
    public ?string $album_cover_linux_path = null;
    public string $album_windows_path;
    public ?string $album_version = null;
    public ?string $original_album = null;
    public int $album_type_id = 1;

    public function __construct()
    {
    }
}
