<?php declare(strict_types=1);

namespace App\Containers\Authentication\Providers;

use Illuminate\Support\ServiceProvider;
use Lcobucci\JWT\Configuration;

/**
 * Подключаем Lcobucci\JWT вручную т.к. через Passport не работает
 */
class JwtServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(Configuration::class, function ($app) {
            return Configuration::forSymmetricSigner(
                new \Lcobucci\JWT\Signer\Hmac\Sha256(), // Алгоритм подписания
                \Lcobucci\JWT\Signer\Key\InMemory::plainText(env('PASSPORT_CLIENT_SECRET')) // Ключ для работы
            );
        });
    }

    public function boot()
    {
        //
    }
}
