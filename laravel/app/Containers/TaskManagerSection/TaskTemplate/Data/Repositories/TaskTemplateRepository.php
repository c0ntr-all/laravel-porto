<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\TaskTemplate\Data\Repositories;

use App\Containers\TaskManagerSection\TaskTemplate\Data\DTO\TaskTemplateCreateData;
use App\Containers\TaskManagerSection\TaskTemplate\Data\DTO\TaskTemplateUpdateData;
use App\Containers\TaskManagerSection\TaskTemplate\Models\TaskTemplate;
use App\Containers\TaskManagerSection\TaskTemplate\Models\TaskTemplateChecklist;
use App\Containers\TaskManagerSection\TaskTemplate\Models\TaskTemplateChecklistItem;
use Illuminate\Database\Eloquent\Collection;
use Spatie\LaravelData\Optional;

class TaskTemplateRepository
{
    public function list(): Collection
    {
        return TaskTemplate::with(['checklists.items'])
            ->orderByDesc('id')
            ->get();
    }

    public function findWithChecklists(int $id): TaskTemplate
    {
        return TaskTemplate::with(['checklists.items'])->findOrFail($id);
    }

    public function create(TaskTemplateCreateData $dto): TaskTemplate
    {
        $template = TaskTemplate::create([
            'user_id' => $dto->user_id,
            'title' => $dto->title,
            'content' => $dto->content,
        ]);

        if ($dto->checklists !== null) {
            $this->syncChecklists($template, $dto->checklists, $dto->user_id);
        }

        return $template->load(['checklists.items']);
    }

    public function update(TaskTemplate $template, TaskTemplateUpdateData $dto): TaskTemplate
    {
        $attributes = [];

        if (!($dto->title instanceof Optional)) {
            $attributes['title'] = $dto->title;
        }

        if (!($dto->content instanceof Optional)) {
            $attributes['content'] = $dto->content;
        }

        if ($attributes !== []) {
            $template->update($attributes);
        }

        if (!($dto->checklists instanceof Optional)) {
            $this->syncChecklists($template, $dto->checklists, $template->user_id);
        }

        return $template->fresh(['checklists.items']);
    }

    public function delete(TaskTemplate $template): ?bool
    {
        return $template->delete();
    }

    /**
     * @param array<int, array{title: string, items?: array<int, array{title: string}>}> $checklists
     */
    private function syncChecklists(TaskTemplate $template, array $checklists, int $userId): void
    {
        $existingChecklists = $template->checklists()->get();

        foreach ($existingChecklists as $checklist) {
            $checklist->items()->delete();
            $checklist->delete();
        }

        foreach ($checklists as $checklistPosition => $checklistData) {
            $checklist = TaskTemplateChecklist::create([
                'user_id' => $userId,
                'task_template_id' => $template->id,
                'title' => $checklistData['title'],
                'position' => $checklistPosition,
            ]);

            foreach ($checklistData['items'] ?? [] as $itemPosition => $itemData) {
                TaskTemplateChecklistItem::create([
                    'user_id' => $userId,
                    'task_template_checklist_id' => $checklist->id,
                    'title' => $itemData['title'],
                    'position' => $itemPosition,
                ]);
            }
        }
    }
}
