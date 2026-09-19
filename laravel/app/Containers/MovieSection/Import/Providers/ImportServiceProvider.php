<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Import\Providers;

use App\Containers\MovieSection\Import\Clients\KinopoiskHttpClient;
use App\Containers\MovieSection\Import\Contracts\KinopoiskFilmPageClientInterface;
use Illuminate\Support\ServiceProvider;

class ImportServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(KinopoiskFilmPageClientInterface::class, KinopoiskHttpClient::class);
    }
}
