<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Import\Models;

use App\Containers\AppSection\User\Models\User;
use App\Containers\MovieSection\Import\Enums\MovieImportStatusEnum;
use App\Containers\MovieSection\Movie\Models\Movie;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property int|null $movie_id
 * @property int $kp_id
 * @property string $source_url
 * @property MovieImportStatusEnum $status
 * @property int|null $http_status
 * @property bool|null $was_created
 * @property array<string, mixed>|null $parsed_payload
 * @property array<string, mixed>|null $meta
 * @property string|null $error_message
 * @property Carbon|null $started_at
 * @property Carbon|null $finished_at
 * @property int|null $duration_ms
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $user
 * @property-read Movie|null $movie
 */
class MovieImport extends Model
{
    use HasFactory;

    protected $table = 'movie_imports';

    protected $fillable = [
        'user_id',
        'movie_id',
        'kp_id',
        'source_url',
        'status',
        'http_status',
        'was_created',
        'parsed_payload',
        'meta',
        'error_message',
        'started_at',
        'finished_at',
        'duration_ms',
    ];

    protected function casts(): array
    {
        return [
            'status' => MovieImportStatusEnum::class,
            'http_status' => 'integer',
            'was_created' => 'boolean',
            'parsed_payload' => 'array',
            'meta' => 'array',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
            'duration_ms' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function movie(): BelongsTo
    {
        return $this->belongsTo(Movie::class);
    }
}
