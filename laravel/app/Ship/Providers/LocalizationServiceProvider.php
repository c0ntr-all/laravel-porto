<?php

namespace App\Ship\Providers;

use App\Ship\Traits\PortoPaths;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class LocalizationServiceProvider extends ServiceProvider
{
    use PortoPaths;

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->loadLocalsFromShip();

        $allContainerPaths = $this->getAllContainerPaths();

        foreach ($allContainerPaths as $containerPath) {
            $this->loadLocalsFromContainers($containerPath);
        }

        if (app()->environment('local')) {
            $this->debugLoadedTranslations();
        }
    }

    public function loadLocalsFromContainers($containerPath): void
    {
        $containerLocaleDirectory = $containerPath . '/Languages';
        $containerName = basename($containerPath);
        $pathParts = explode(DIRECTORY_SEPARATOR, $containerPath);
        $sectionName = $pathParts[count($pathParts) - 2];

        $this->loadLocals($containerLocaleDirectory, $containerName, $sectionName);
    }

    private function loadLocals($directory, $containerName, $sectionName = null): void
    {
        if (File::isDirectory($directory)) {
            $namespace = $this->buildLocaleNamespace($sectionName, $containerName);

            if (app()->environment('local')) {
                $this->logLoadedDirectory($directory, $namespace);
            }

            $this->loadTranslationsFrom($directory, $namespace);
            $this->loadJsonTranslationsFrom($directory);
        } elseif (app()->environment('local')) {
            logger()->warning("Directory not found: {$directory}");
        }
    }

    private function buildLocaleNamespace(string|null $sectionName, string $containerName): string
    {
        return $sectionName ? (Str::camel($sectionName) . '@' . Str::camel($containerName)) : Str::camel($containerName);
    }

    public function loadLocalsFromShip(): void
    {
        $shipLocaleDirectory = base_path('app/Ship/Languages');
        $this->loadLocals($shipLocaleDirectory, 'ship');
    }

    /**
     * Отладочный метод для логирования загруженных директорий
     */
    private function logLoadedDirectory(string $directory, string $namespace): void
    {
        $languages = [];
        if (File::isDirectory($directory)) {
            $directories = File::directories($directory);
            foreach ($directories as $dir) {
                $languages[] = basename($dir);
            }

            $jsonFiles = File::files($directory);
            foreach ($jsonFiles as $file) {
                if ($file->getExtension() === 'json') {
                    $languages[] = $file->getBasename('.json');
                }
            }

            $languages = array_unique($languages);

            logger()->info("Loaded translations", [
                'directory' => $directory,
                'namespace' => $namespace,
                'languages' => $languages,
                'files_count' => count(File::allFiles($directory))
            ]);
        }
    }

    /**
     * Отладочный метод для проверки всех загруженных переводов
     */
    private function debugLoadedTranslations(): void
    {
        $loadedNamespaces = Lang::getLoader()->namespaces();

        logger()->info("=== Localization Debug Info ===");
        logger()->info("Loaded namespaces:", array_keys($loadedNamespaces));

        $localesToCheck = ['en', 'ru', 'uk'];

        foreach ($localesToCheck as $locale) {
            app()->setLocale($locale);
            logger()->info("Checking locale: {$locale}");

            foreach ($loadedNamespaces as $namespace => $path) {
                $key = "{$namespace}::test";
                $translation = trans($key, [], $locale);

                if ($translation !== $key) {
                    logger()->info("  ✓ Namespace '{$namespace}' has translations for {$locale}");
                } else {
                    logger()->warning("  ✗ Namespace '{$namespace}' has NO translations for {$locale}");
                }
            }
        }

        $allFiles = [];
        foreach ($loadedNamespaces as $namespace => $path) {
            if (File::isDirectory($path)) {
                $files = File::allFiles($path);
                foreach ($files as $file) {
                    $relativePath = $file->getRelativePathname();
                    $allFiles[$namespace][] = $relativePath;
                }
            }
        }

        logger()->info("All loaded translation files:", $allFiles);
    }
}
