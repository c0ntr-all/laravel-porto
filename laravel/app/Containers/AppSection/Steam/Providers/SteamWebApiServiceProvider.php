<?php

declare(strict_types=1);

namespace App\Containers\AppSection\Steam\Providers;

use App\Containers\AppSection\Steam\Clients\SteamWebApiHttpClient;
use App\Containers\AppSection\Steam\Contracts\SteamWebApiClientInterface;
use Illuminate\Support\ServiceProvider;

class SteamWebApiServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(SteamWebApiClientInterface::class, SteamWebApiHttpClient::class);
    }
}
