<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Folder\Providers;

use App\Containers\AppSection\User\Models\User;
use App\Containers\MovieSection\Folder\Tasks\EnsureSystemMovieFoldersTask;
use Illuminate\Support\ServiceProvider;

class FolderServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        User::created(function (User $user): void {
            app(EnsureSystemMovieFoldersTask::class)->run((int) $user->id);
        });
    }
}
