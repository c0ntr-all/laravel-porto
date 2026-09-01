<?php declare(strict_types=1);

namespace App\Containers\DashboardSection\Widget\Tasks;

use App\Containers\DashboardSection\Widget\Contracts\WidgetContract;
use App\Containers\DashboardSection\Widget\Managers\WidgetRegistry;
use App\Ship\Parents\Tasks\Task as ParentTask;
use App\Ship\Traits\PortoPaths;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\File;
use ReflectionClass;
use Symfony\Component\Finder\SplFileInfo;
use Throwable;

class DiscoverWidgetsTask extends ParentTask
{
    use PortoPaths;

    public function __construct(
        private readonly Application $app,
        private readonly WidgetRegistry $widgetRegistry,
    ) {
    }

    public function run(): void
    {
        foreach ($this->getAllContainerPaths() as $containerPath) {
            $widgetsDirectory = $containerPath . DIRECTORY_SEPARATOR . 'Widgets';

            if (!File::isDirectory($widgetsDirectory)) {
                continue;
            }

            foreach (File::allFiles($widgetsDirectory) as $file) {
                $class = $this->classFromFile($containerPath, $file);

                if ($class === null) {
                    continue;
                }

                $this->registerClass($class);
            }
        }
    }

    private function classFromFile(string $containerPath, SplFileInfo $file): ?string
    {
        $relative = str_replace($containerPath . DIRECTORY_SEPARATOR, '', $file->getPathname());
        $relative = str_replace(['/', '\\'], '\\', $relative);
        $relative = preg_replace('/\.php$/', '', $relative) ?? $relative;

        $appRelative = str_replace(app_path() . DIRECTORY_SEPARATOR, '', $containerPath);
        $class = 'App\\' . str_replace(['/', '\\'], '\\', $appRelative) . '\\' . $relative;

        return class_exists($class) ? $class : null;
    }

    /**
     * @param class-string $class
     */
    private function registerClass(string $class): void
    {
        try {
            $reflection = new ReflectionClass($class);
        } catch (Throwable) {
            return;
        }

        if ($reflection->isAbstract() || !$reflection->implementsInterface(WidgetContract::class)) {
            return;
        }

        /** @var WidgetContract $widget */
        $widget = $this->app->make($class);
        $this->widgetRegistry->register($widget);
    }
}
