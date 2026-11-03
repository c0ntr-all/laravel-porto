<?php

declare(strict_types=1);

namespace App\Containers\NotesSection\Note\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property int $source_note_id
 * @property int $target_note_id
 * @property string $label
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Note $source
 * @property-read Note|null $target
 */
class NoteLink extends Model
{
    protected $table = 'note_links';

    protected $fillable = [
        'user_id',
        'source_note_id',
        'target_note_id',
        'label',
    ];

    public function source(): BelongsTo
    {
        return $this->belongsTo(Note::class, 'source_note_id')->withoutGlobalScope('user');
    }

    public function target(): BelongsTo
    {
        return $this->belongsTo(Note::class, 'target_note_id')->withoutGlobalScope('user');
    }
}
