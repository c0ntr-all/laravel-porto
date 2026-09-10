<?php declare(strict_types=1);

namespace App\Containers\AppSection\CustomField\Providers;

use App\Containers\AppSection\CustomField\Modules\ListCustomFieldModule;
use App\Containers\AppSection\CustomField\Services\CustomFieldModuleRegistry;
use Illuminate\Support\ServiceProvider;

class CustomFieldServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->tag([
            ListCustomFieldModule::class,
        ], 'custom-field.modules');

        $this->app->singleton(CustomFieldModuleRegistry::class, function ($app) {
            return new CustomFieldModuleRegistry($app->tagged('custom-field.modules'));
        });
    }
}
