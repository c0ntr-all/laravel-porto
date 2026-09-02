<?php declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

use App\Containers\AppSection\User\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('music.uploads.{uploadId}', function (User $user, int $uploadId) {
    return $user->hasRole('admin') ? ['id' => $user->id] : false;
});

Broadcast::channel('users.{userId}.notifications', function (User $user, int $userId) {
    return (int) $user->id === $userId ? ['id' => $user->id] : false;
});
