<?php declare(strict_types=1);

namespace App\Ship\Providers;

use App\Ship\Traits\PortoPaths;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Traits\ForwardsCalls;
use Symfony\Component\Finder\SplFileInfo;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as LaravelRouteServiceProvider;

class RouteServiceProvider extends LaravelRouteServiceProvider
{
    use ForwardsCalls,
        PortoPaths;

    /**
     * Register any application services.
     */
    public function register(): void
    {
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->runRoutesAutoLoader();
    }

    /**
     * Define the routes for the application.
     */
    public function map(): void
    {
    }

    public function runRoutesAutoLoader(): void
    {
        if (!$this->shouldRegisterRoutes()) {
            return;
        }

        $allContainerPaths = $this->getAllContainerPaths();

        foreach ($allContainerPaths as $containerPath) {
            $this->loadApiContainerRoutes($containerPath);
            $this->loadWebContainerRoutes($containerPath);
        }
    }

    private function shouldRegisterRoutes(): bool
    {
        if ($this->app->routesAreCached()) {
            return false;
        }

        return true;
    }

    /**
     * Register the Containers API routes files.
     */
    private function loadApiContainerRoutes(string $containerPath): void
    {
        $apiRoutesPath = $this->getRoutePathsForUI($containerPath, 'API');

        if (File::isDirectory($apiRoutesPath)) {
            $files = $this->getFilesSortedByName($apiRoutesPath);
            foreach ($files as $file) {
                $this->loadApiRoute($file);
            }
        }
    }

    /**
     * @return array|SplFileInfo[]
     */
    private function getFilesSortedByName(string $apiRoutesPath): array
    {
        $files = File::allFiles($apiRoutesPath);

        return Arr::sort($files, function ($file) {
            return $file->getFilename();
        });
    }

    private function getRoutePathsForUI(string $containerPath, string $ui): string
    {
        return $this->getUIPathForContainer($containerPath, $ui) . DIRECTORY_SEPARATOR . 'Routes';
    }

    private function getUIPathForContainer(string $containerPath, string $ui): string
    {
        return $containerPath . DIRECTORY_SEPARATOR . 'UI' . DIRECTORY_SEPARATOR . $ui;
    }

    /**
     * Register the Containers WEB routes files.
     */
    private function loadWebContainerRoutes($containerPath): void
    {
        $webRoutesPath = $this->getRoutePathsForUI($containerPath, 'WEB');

        if (File::isDirectory($webRoutesPath)) {
            $files = $this->getFilesSortedByName($webRoutesPath);
            foreach ($files as $file) {
                $this->loadWebRoute($file);
            }
        }
    }

    private function loadApiRoute(SplFileInfo $file): void
    {
        $routeGroupArray = $this->getApiRouteGroup($file);

        Route::group($routeGroupArray, function ($router) use ($file) {
            require $file->getPathname();
        });
    }

    private function loadWebRoute(SplFileInfo $file): void
    {
        Route::group([
            'middleware' => ['web'],
        ], function ($router) use ($file) {
            require $file->getPathname();
        });
    }

    public function getApiRouteGroup(SplFileInfo|string $endpointFileOrPrefixString): array
    {
        $prefix = is_string($endpointFileOrPrefixString) ? $endpointFileOrPrefixString : $this->getApiVersionPrefix($endpointFileOrPrefixString);

        return [
            'middleware' => $this->getMiddlewares(),
//            'domain' => $this->getApiUrl(),
            //todo: Temporary solution for api routes
            'prefix' => 'api' . $prefix,
        ];
    }

    private function getMiddlewares(): array
    {
        return array_filter([
            'api',
            $this->getRateLimitMiddleware(), // Returns NULL if feature disabled. Null will be removed form the array.
        ]);
    }

    private function getApiVersionPrefix(SplFileInfo $file): string
    {
        return '/' . $this->getRouteFileVersionFromFileName($file);
    }

    private function getApiUrl(): string
    {
        return Config::get('api.url');
    }

    private function getRateLimitMiddleware(): string|null
    {
        $rateLimitMiddleware = null;

        if (Config::get('api.throttle.enabled')) {
            RateLimiter::for('api', function (Request $request) {
                return Limit::perMinutes(Config::get('api.throttle.expires'), Config::get('api.throttle.attempts'))->by($request->user()?->id ?: $request->ip());
            });

            $rateLimitMiddleware = 'throttle:api';
        }

        return $rateLimitMiddleware;
    }

    private function getRouteFileVersionFromFileName(SplFileInfo $file): string|bool
    {
        $fileNameWithoutExtension = $this->getRouteFileNameWithoutExtension($file);

        $fileNameWithoutExtensionExploded = explode('.', $fileNameWithoutExtension);

        end($fileNameWithoutExtensionExploded);

        // get the array before the last one
        return prev($fileNameWithoutExtensionExploded);
    }

    private function getRouteFileNameWithoutExtension(SplFileInfo $file): string
    {
        return pathinfo($file->getFilename(), PATHINFO_FILENAME);
    }
}
