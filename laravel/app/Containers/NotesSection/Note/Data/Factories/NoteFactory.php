<?php

declare(strict_types=1);

namespace App\Containers\NotesSection\Note\Data\Factories;

use App\Containers\AppSection\User\Models\User;
use App\Containers\NotesSection\Note\Models\Note;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Note>
 */
class NoteFactory extends Factory
{
    protected $model = Note::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->sentence(3),
            'content' => '<p>'.fake()->paragraph().'</p>',
        ];
    }
}
