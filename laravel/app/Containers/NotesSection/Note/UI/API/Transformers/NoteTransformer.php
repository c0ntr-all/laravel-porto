<?php

declare(strict_types=1);

namespace App\Containers\NotesSection\Note\UI\API\Transformers;

use App\Containers\NotesSection\Note\Models\Note;
use App\Containers\NotesSection\Note\Models\NoteLink;
use App\Containers\NotesSection\Note\Support\NoteLinkParser;
use App\Containers\NotesSection\Note\Support\NotePlainText;
use League\Fractal\TransformerAbstract;

class NoteTransformer extends TransformerAbstract
{
    public function transform(Note $note): array
    {
        $plain = $note->content_raw ?? NotePlainText::fromHtml($note->content);

        return [
            'id' => (string) $note->id,
            'name' => $note->name,
            'content' => $note->content,
            'content_raw' => $plain,
            'excerpt' => NotePlainText::excerpt($plain),
            'parent_id' => $note->parent_id === null ? null : (string) $note->parent_id,
            'children_count' => (int) ($note->children_count ?? 0),
            'is_pinned' => $note->pin_position !== null,
            'pin_position' => $note->pin_position === null ? null : (int) $note->pin_position,
            'wiki_links' => $this->links($note),
            'backlinks' => $this->backlinks($note),
            'unresolved_links' => $this->unresolved($note),
            'ambiguous_links' => $note->ambiguousLinks,
            'created_at' => $note->created_at?->format(Note::TIMESTAMP_FORMAT),
            'updated_at' => $note->timestamp(),
        ];
    }

    /**
     * @return list<array{id: string, name: string|null, label: string}>
     */
    private function links(Note $note): array
    {
        if (! $note->relationLoaded('outgoingLinks')) {
            return [];
        }

        return $note->outgoingLinks
            ->map(fn (NoteLink $link) => [
                'id' => (string) $link->target_note_id,
                'name' => $link->target?->name ?? $link->label,
                'label' => $link->label,
            ])
            ->values()
            ->all();
    }

    /**
     * @return list<array{id: string, name: string|null, label: string}>
     */
    private function backlinks(Note $note): array
    {
        if (! $note->relationLoaded('incomingLinks')) {
            return [];
        }

        return $note->incomingLinks
            ->filter(fn (NoteLink $link) => $link->source !== null)
            ->map(fn (NoteLink $link) => [
                'id' => (string) $link->source_note_id,
                'name' => $link->source?->name,
                'label' => $link->label,
            ])
            ->values()
            ->all();
    }

    /**
     * @return list<string>
     */
    private function unresolved(Note $note): array
    {
        if (! $note->relationLoaded('outgoingLinks')) {
            return [];
        }

        $stored = $note->outgoingLinks
            ->map(fn (NoteLink $link) => mb_strtolower($link->label))
            ->all();

        return array_values(array_filter(
            NoteLinkParser::labels($note->content_raw),
            static fn (string $label): bool => ! in_array(mb_strtolower($label), $stored, true),
        ));
    }
}
