<?php

declare(strict_types=1);

namespace App\Containers\NotesSection\Note\Data\DTO;

use App\Ship\Parents\DTO\Data;

class CreateNoteDto extends Data
{
    public int $user_id;

    public string $name;

    public ?string $content = null;

    public ?int $parent_id = null;

    public function __construct() {}
}
