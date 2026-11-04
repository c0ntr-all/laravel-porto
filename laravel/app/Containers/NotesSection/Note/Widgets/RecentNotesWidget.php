<?php

declare(strict_types=1);

namespace App\Containers\NotesSection\Note\Widgets;

use App\Containers\DashboardSection\Widget\Abstracts\AbstractWidget;
use App\Containers\DashboardSection\Widget\Data\ValueObjects\WidgetContext;
use App\Containers\DashboardSection\Widget\Data\ValueObjects\WidgetPayload;
use App\Containers\DashboardSection\Widget\Enums\WidgetSizeEnum;
use App\Containers\NotesSection\Note\Models\Note;
use App\Containers\NotesSection\Note\Support\NotePlainText;

class RecentNotesWidget extends AbstractWidget
{
    public function type(): string
    {
        return 'notes.recent-notes';
    }

    public function name(): string
    {
        return 'Последние заметки';
    }

    public function description(): string
    {
        return 'Недавно изменённые заметки.';
    }

    public function icon(): string
    {
        return 'sticky_note_2';
    }

    public function defaultSize(): WidgetSizeEnum
    {
        return WidgetSizeEnum::THIRD;
    }

    public function configSchema(): array
    {
        return $this->limitSchema(5, 20);
    }

    public function resolve(WidgetContext $context): WidgetPayload
    {
        $notes = Note::query()
            ->forUser((int) $context->user->id)
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->limit($context->limit())
            ->get();

        return $this->listPayload($context, $this->items($notes), ['href' => '/notes']);
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Collection<int, Note>  $notes
     * @return list<array{id: string, title: string, subtitle: string|null, href: string}>
     */
    private function items($notes): array
    {
        return $notes->map(function (Note $note): array {
            $subtitle = NotePlainText::excerpt($note->content_raw, 80);

            return [
                'id' => (string) $note->id,
                'title' => $note->name,
                'subtitle' => $subtitle !== '' ? $subtitle : null,
                'href' => '/notes/'.$note->id,
            ];
        })->values()->all();
    }
}
