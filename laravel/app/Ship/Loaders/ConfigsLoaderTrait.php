<?php declare(strict_types=1);

namespace App\Ship\Loaders;

use App\Ship\Traits\PortoPaths;
use Illuminate\Support\Facades\File;

trait ConfigsLoaderTrait
{
    use PortoPaths;

    public function runConfigLoader(): void
    {
        $this->loadConfigsFromShip();

        foreach ($this->getAllContainerPaths() as $containerPath) {
            $this->loadConfigsFromContainers($containerPath);
        }
    }

    public function loadConfigsFromShip(): void
    {
        $shipConfigsDirectory = base_path('app/Ship/Configs');
        $this->loadConfigs($shipConfigsDirectory);
    }

    public function loadConfigsFromContainers($containerPath): void
    {
        $containerConfigsDirectory = $containerPath . '/Configs';
        $this->loadConfigs($containerConfigsDirectory);
    }

    private function loadConfigs($configFolder): void
    {
        if (File::isDirectory($configFolder)) {
            $files = File::files($configFolder);

            foreach ($files as $file) {
                $name = File::name($file);
                $path = $configFolder . '/' . $name . '.php';

                $this->mergeConfigFrom($path, $name);
            }
        }
    }
}
