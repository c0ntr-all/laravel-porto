<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Movie\Data\DTO;

use App\Containers\MovieSection\Movie\Enums\MovieTypeEnum;
use App\Ship\Parents\DTO\Data;
use Spatie\LaravelData\Optional;

class MovieUpdateData extends Data
{
    public int|Optional $kp_id;
    public string|Optional $title;
    public int|Optional $year;
    public MovieTypeEnum|Optional $type;
    public string|Optional|null $cover;
    public float|Optional|null $kp_rating;
    public string|Optional|null $kp_img;

    public function __construct()
    {
    }
}
