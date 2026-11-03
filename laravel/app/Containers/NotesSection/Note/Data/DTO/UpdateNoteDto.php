<?php

declare(strict_types=1);

namespace App\Containers\NotesSection\Note\Data\DTO;

use App\Ship\Parents\DTO\Data;
use Spatie\LaravelData\Optional;

class UpdateNoteDto extends Data
{
    public string|Optional $name;

    public string|Optional|null $content;

    public int|Optional|null $parent_id;

    public string $updated_at;

    public function __construct() {}
}
