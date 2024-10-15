<?php declare(strict_types=1);

namespace App\Ship\Traits;

use Illuminate\Support\Facades\File;

trait PortoPaths
{
    private const SHIP_NAME = 'ship';
    private const CONTAINERS_DIRECTORY_NAME = 'Containers';

    private function getAllContainerPaths(): array
    {
        $sectionNames = $this->getSectionNames();
        $containerPaths = [];
        foreach ($sectionNames as $name) {
            $sectionContainerPaths = $this->getSectionContainerPaths($name);
            foreach ($sectionContainerPaths as $containerPath) {
                $containerPaths[] = $containerPath;
            }
        }

        return $containerPaths;
    }

    public function getSectionNames(): array
    {
        $sectionNames = [];

        foreach ($this->getContainersPaths() as $sectionPath) {
            $sectionNames[] = basename($sectionPath);
        }

        return $sectionNames;
    }

    public function getContainersPaths(): array
    {
        return File::directories(app_path(self::CONTAINERS_DIRECTORY_NAME));
    }

    public function getSectionContainerPaths(string $sectionName): array
    {
        return File::directories(app_path(self::CONTAINERS_DIRECTORY_NAME . DIRECTORY_SEPARATOR . $sectionName));
    }
}
