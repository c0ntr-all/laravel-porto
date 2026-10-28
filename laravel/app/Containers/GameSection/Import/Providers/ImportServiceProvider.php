<?php

declare(strict_types=1);

namespace App\Containers\GameSection\Import\Providers;

use App\Containers\GameSection\Import\Clients\SteamStoreHttpClient;
use App\Containers\GameSection\Import\Contracts\SteamStoreClientInterface;
use Illuminate\Support\ServiceProvider;

class ImportServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(SteamStoreClientInterface::class, SteamStoreHttpClient::class);
    }
}
