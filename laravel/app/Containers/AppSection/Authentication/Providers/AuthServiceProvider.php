<?php declare(strict_types=1);

namespace App\Containers\AppSection\Authentication\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as LaravelAuthServiceProvider;
use Laravel\Passport\Passport;

class AuthServiceProvider extends LaravelAuthServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        parent::register();
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        Passport::ignoreRoutes();
        Passport::enablePasswordGrant();
//        Passport::tokensExpireIn(now()->addDays(15));
//        Passport::refreshTokensExpireIn(now()->addDays(30));
//        Passport::personalAccessTokensExpireIn(now()->addMonths(6));
    }
}
