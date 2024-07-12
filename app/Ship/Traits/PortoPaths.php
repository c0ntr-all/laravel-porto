<?php

namespace App\Ship\Traits;

use Illuminate\Support\Facades\File;

trait PortoPaths
{
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
        return File::directories(app_path('Containers'));
    }

    public function getSectionContainerPaths(string $sectionName): array
    {
        return File::directories(app_path('Containers' . DIRECTORY_SEPARATOR . $sectionName));
    }
}
