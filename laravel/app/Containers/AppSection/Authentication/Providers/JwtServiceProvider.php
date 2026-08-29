<?php declare(strict_types=1);

namespace App\Containers\AppSection\Authentication\Providers;

use Illuminate\Support\ServiceProvider;
use Lcobucci\JWT\Configuration;

/**
 * Подключаем Lcobucci\JWT вручную т.к. через Passport не работает
 */
class JwtServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(Configuration::class, function () {
            $secret = config('passport.personal_access_client.secret');

            if (!is_string($secret) || $secret === '') {
                throw new \RuntimeException(
                    'Passport personal access client secret is not configured. Set PASSPORT_PERSONAL_ACCESS_CLIENT_SECRET in your .env file.'
                );
            }

            return Configuration::forSymmetricSigner(
                new \Lcobucci\JWT\Signer\Hmac\Sha256(),
                \Lcobucci\JWT\Signer\Key\InMemory::plainText($secret)
            );
        });
    }

    public function boot()
    {
        //
    }
}
