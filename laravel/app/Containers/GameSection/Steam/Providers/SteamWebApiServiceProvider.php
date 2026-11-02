<?php

declare(strict_types=1);

namespace App\Containers\GameSection\Steam\Providers;

use App\Containers\GameSection\Steam\Clients\SteamWebApiHttpClient;
use App\Containers\GameSection\Steam\Contracts\SteamWebApiClientInterface;
use Illuminate\Support\ServiceProvider;

class SteamWebApiServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(SteamWebApiClientInterface::class, SteamWebApiHttpClient::class);
    }
}
