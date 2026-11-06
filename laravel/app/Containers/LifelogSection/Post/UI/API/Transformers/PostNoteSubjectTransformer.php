<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Post\UI\API\Transformers;

use App\Containers\NotesSection\Note\Models\Note;
use App\Containers\NotesSection\Note\Support\NotePlainText;
use League\Fractal\TransformerAbstract;

class PostNoteSubjectTransformer extends TransformerAbstract
{
    public function transform(Note $note): array
    {
        $plain = $note->content_raw ?? NotePlainText::fromHtml($note->content);

        return [
            'id' => (string) $note->id,
            'name' => $note->name,
            'excerpt' => NotePlainText::excerpt($plain),
        ];
    }
}
