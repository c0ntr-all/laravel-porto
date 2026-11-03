<?php

declare(strict_types=1);

namespace App\Containers\NotesSection\Note\Data\DTO;

use App\Ship\Parents\DTO\Data;

class MovePinnedNoteDto extends Data
{
    public ?int $before_id = null;

    public function __construct() {}
}
