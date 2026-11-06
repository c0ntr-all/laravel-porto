<?php

declare(strict_types=1);

namespace App\Containers\NotesSection\Note\Models;

use App\Containers\AppSection\User\Models\Traits\HasUser;
use App\Containers\AppSection\User\Models\User;
use App\Containers\NotesSection\Note\Support\NotePlainText;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Models\ActivityLoggableModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property int|null $parent_id
 * @property string $name
 * @property string|null $content
 * @property string|null $content_raw
 * @property int|null $pin_position
 * @property int|null $children_count
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read User $user
 * @property-read Note|null $parentNote
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Note> $children
 * @property-read \Illuminate\Database\Eloquent\Collection<int, NoteLink> $outgoingLinks
 * @property-read \Illuminate\Database\Eloquent\Collection<int, NoteLink> $incomingLinks
 *
 * @mixin \Eloquent
 */
class Note extends ActivityLoggableModel
{
    use HasFactory,
        HasUser,
        SoftDeletes;

    public const NAME_MAX_LENGTH = 255;

    public const CONTENT_MAX_LENGTH = 200_000;

    public const TIMESTAMP_FORMAT = 'Y-m-d H:i:s.u';

    /**
     * Titles that currently match more than one note. Filled when one note is read or saved.
     *
     * @var list<string>
     */
    public array $ambiguousLinks = [];

    protected ContainerAliasEnum $loggableType = ContainerAliasEnum::NOTE;

    protected $table = 'notes';

    protected $dateFormat = self::TIMESTAMP_FORMAT;

    protected $fillable = [
        'user_id',
        'parent_id',
        'name',
        'content',
        'content_raw',
        'pin_position',
    ];

    protected static function booted(): void
    {
        static::saving(function (Note $note): void {
            if ($note->isDirty('content')) {
                $note->content_raw = NotePlainText::fromHtml($note->content);
            }
        });
    }

    public function resolveRouteBinding($value, $field = null)
    {
        $field ??= $this->getRouteKeyName();

        return $this->newQuery()
            ->where($this->qualifyColumn($field), $value)
            ->where($this->qualifyColumn('user_id'), auth()->id())
            ->first();
    }

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where($query->qualifyColumn('user_id'), $userId);
    }

    public function scopePinned(Builder $query): Builder
    {
        return $query->whereNotNull($query->qualifyColumn('pin_position'));
    }

    public function scopeRoots(Builder $query): Builder
    {
        return $query->whereNull($query->qualifyColumn('parent_id'));
    }

    public function parentNote(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function outgoingLinks(): HasMany
    {
        return $this->hasMany(NoteLink::class, 'source_note_id');
    }

    public function incomingLinks(): HasMany
    {
        return $this->hasMany(NoteLink::class, 'target_note_id');
    }

    public function timestamp(): ?string
    {
        return $this->updated_at?->format(self::TIMESTAMP_FORMAT);
    }
}
