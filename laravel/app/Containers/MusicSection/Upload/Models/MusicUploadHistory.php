<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Upload\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Music\MusicHistory
 *
 * @property string $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class MusicUploadHistory extends Model
{
    protected $table = 'music_upload_history';

    protected $guarded = [];
}
