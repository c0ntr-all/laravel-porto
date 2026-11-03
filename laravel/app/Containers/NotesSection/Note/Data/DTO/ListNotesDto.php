<?php

declare(strict_types=1);

namespace App\Containers\NotesSection\Note\Data\DTO;

use App\Ship\Parents\DTO\Data;

class ListNotesDto extends Data
{
    public int $user_id;

    public int $per_page = 20;

    public function __construct() {}
}
