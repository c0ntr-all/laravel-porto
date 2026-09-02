<?php declare(strict_types=1);

namespace App\Containers\AppSection\User\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Containers\AppSection\Notification\Contracts\ReceivesNotifications;
use App\Containers\AppSection\Notification\Models\Traits\HasUserNotifications;
use App\Containers\AppSection\User\Models\Traits\HasAvatar;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

/**
 * App\Containers\AppSection\User\Models
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string|null $avatar
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class User extends Authenticatable implements ReceivesNotifications
{
    use HasFactory,
        Notifiable,
        HasApiTokens,
        HasRoles,
        HasAvatar,
        HasUserNotifications;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function getNotificationUserId(): int
    {
        return (int) $this->id;
    }
}
