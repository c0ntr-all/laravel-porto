<?php

declare(strict_types=1);

namespace App\Containers\NotesSection\Note\Tasks;

use App\Containers\NotesSection\Note\Models\Note;
use App\Containers\NotesSection\Note\Support\NoteLinkParser;
use App\Ship\Parents\Tasks\Task as ParentTask;

class ResolveNoteLinksTask extends ParentTask
{
    /**
     * @return array{
     *     links: list<array{id: int, name: string, label: string}>,
     *     unresolved: list<string>,
     *     ambiguous: list<string>
     * }
     */
    public function run(Note $note): array
    {
        $labels = NoteLinkParser::labels($note->content_raw);
        if ($labels === []) {
            return [
                'links' => [],
                'unresolved' => [],
                'ambiguous' => [],
            ];
        }

        $matches = Note::query()
            ->withoutGlobalScope('user')
            ->forUser((int) $note->user_id)
            ->when($note->exists, fn ($query) => $query->whereKeyNot($note->id))
            ->whereIn('name', $labels)
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->get(['id', 'name', 'updated_at']);

        $groups = [];
        foreach ($matches as $match) {
            $groups[mb_strtolower($match->name)][] = $match;
        }

        $links = [];
        $unresolved = [];
        $ambiguous = [];
        $seenTargets = [];

        foreach ($labels as $label) {
            $group = $groups[mb_strtolower($label)] ?? [];
            if ($group === []) {
                $unresolved[] = $label;

                continue;
            }

            if (count($group) > 1) {
                $ambiguous[] = $label;
            }

            /** @var Note $target */
            $target = $group[0];
            if (isset($seenTargets[$target->id])) {
                continue;
            }

            $seenTargets[$target->id] = true;
            $links[] = [
                'id' => (int) $target->id,
                'name' => $target->name,
                'label' => $label,
            ];
        }

        return [
            'links' => $links,
            'unresolved' => $unresolved,
            'ambiguous' => $ambiguous,
        ];
    }
}
