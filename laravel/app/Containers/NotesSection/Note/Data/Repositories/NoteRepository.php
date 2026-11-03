<?php

declare(strict_types=1);

namespace App\Containers\NotesSection\Note\Data\Repositories;

use App\Containers\NotesSection\Note\Data\DTO\CreateNoteDto;
use App\Containers\NotesSection\Note\Data\Filters\TextFilter;
use App\Containers\NotesSection\Note\Exceptions\NoteConflictException;
use App\Containers\NotesSection\Note\Models\Note;
use App\Ship\Parents\QueryBuilder\QueryBuilder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedFilter;

class NoteRepository
{
    public function list(int $userId, int $perPage): CursorPaginator
    {
        return QueryBuilder::for(
            Note::query()
                ->forUser($userId)
                ->with([
                    'outgoingLinks.target',
                    'incomingLinks.source',
                ])
                ->withCount('children'),
        )
            ->allowedFilters([
                AllowedFilter::custom('text', new TextFilter),
                AllowedFilter::exact('parent_id'),
                AllowedFilter::callback('pinned', function (Builder $query, mixed $value): void {
                    $pinned = $this->flag($value);
                    if ($pinned === null) {
                        return;
                    }

                    $pinned
                        ? $query->whereNotNull('pin_position')
                        : $query->whereNull('pin_position');
                }),
                AllowedFilter::callback('roots', function (Builder $query, mixed $value): void {
                    if ($this->flag($value) === true) {
                        $query->whereNull('parent_id');
                    }
                }),
            ])
            ->allowedSorts(['name', 'created_at', 'updated_at', 'pin_position'])
            ->defaultSort('-updated_at')
            ->cursorPaginate($perPage);
    }

    public function loadForResponse(Note $note): Note
    {
        return $note->loadCount('children')->load([
            'outgoingLinks.target',
            'incomingLinks.source',
        ]);
    }

    public function create(CreateNoteDto $dto): Note
    {
        return Note::query()->create([
            'user_id' => $dto->user_id,
            'parent_id' => $dto->parent_id,
            'name' => $dto->name,
            'content' => $dto->content,
        ]);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function updateIfUnchanged(Note $note, array $attributes, string $expectedUpdatedAt): Note
    {
        return DB::transaction(function () use ($note, $attributes, $expectedUpdatedAt): Note {
            $locked = Note::query()->whereKey($note->id)->lockForUpdate()->first();
            if (! $locked instanceof Note) {
                throw (new ModelNotFoundException)->setModel(Note::class, [$note->id]);
            }

            $actual = $locked->timestamp() ?? '';
            if ($actual !== $expectedUpdatedAt) {
                throw new NoteConflictException($actual);
            }

            if ($attributes !== []) {
                $locked->fill($attributes);
                $locked->save();
            }

            return $locked;
        });
    }

    public function delete(Note $note): bool
    {
        return (bool) $note->delete();
    }

    public function promoteChildren(Note $note): void
    {
        Note::query()
            ->forUser((int) $note->user_id)
            ->where('parent_id', $note->id)
            ->update([
                'parent_id' => $note->parent_id,
                'updated_at' => now(),
            ]);
    }

    public function clearPin(Note $note): void
    {
        Note::query()->whereKey($note->id)->toBase()->update([
            'pin_position' => null,
        ]);
    }

    /**
     * @return Collection<int, Note>
     */
    public function pinnedForUpdate(int $userId): Collection
    {
        return Note::query()
            ->forUser($userId)
            ->whereNotNull('pin_position')
            ->orderBy('id')
            ->lockForUpdate()
            ->get();
    }

    public function setPinPosition(Note $note, ?int $position): void
    {
        Note::query()->whereKey($note->id)->toBase()->update([
            'pin_position' => $position,
        ]);
    }

    /**
     * @param  array<int, int>  $positions
     */
    public function setPinPositions(array $positions): void
    {
        foreach ($positions as $id => $position) {
            Note::query()->whereKey($id)->toBase()->update([
                'pin_position' => $position,
            ]);
        }
    }

    private function flag(mixed $value): ?bool
    {
        if (is_array($value)) {
            $value = $value[0] ?? null;
        }

        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value)) {
            return match ($value) {
                1 => true,
                0 => false,
                default => null,
            };
        }

        if (! is_string($value)) {
            return null;
        }

        $flag = filter_var($value, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);

        return is_bool($flag) ? $flag : null;
    }
}
