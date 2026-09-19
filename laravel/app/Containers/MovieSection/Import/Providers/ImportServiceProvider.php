<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Import\Providers;

use App\Containers\MovieSection\Import\Clients\PoiskKinoHttpClient;
use App\Containers\MovieSection\Import\Contracts\KinopoiskMovieApiClientInterface;
use Illuminate\Support\ServiceProvider;

class ImportServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(KinopoiskMovieApiClientInterface::class, PoiskKinoHttpClient::class);
    }
}
