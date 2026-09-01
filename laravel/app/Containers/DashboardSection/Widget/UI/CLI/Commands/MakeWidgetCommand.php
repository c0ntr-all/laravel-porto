<?php declare(strict_types=1);

namespace App\Containers\DashboardSection\Widget\UI\CLI\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Filesystem\Filesystem;

class MakeWidgetCommand extends Command
{
    protected $signature = 'make:dashboard-widget
                            {container : Container path, e.g. LifelogSection/Post}
                            {name : Widget class name without suffix, e.g. MoodStats}';

    protected $description = 'Create a custom dashboard widget in a Porto container';

    public function __construct(
        private readonly Filesystem $files,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $container = trim(str_replace('\\', '/', $this->argument('container')), '/');
        $name = Str::studly((string) $this->argument('name'));
        $class = str_ends_with($name, 'Widget') ? $name : $name . 'Widget';

        $containerPath = app_path('Containers/' . $container);
        if (!$this->files->isDirectory($containerPath)) {
            $this->error("Container not found: {$containerPath}");

            return self::FAILURE;
        }

        $widgetsPath = $containerPath . '/Widgets';
        $this->files->ensureDirectoryExists($widgetsPath);

        $target = $widgetsPath . '/' . $class . '.php';
        if ($this->files->exists($target)) {
            $this->error("Widget already exists: {$target}");

            return self::FAILURE;
        }

        $namespace = 'App\\Containers\\' . str_replace('/', '\\', $container) . '\\Widgets';
        $typePrefix = $this->typePrefix($container);
        $typeSuffix = Str::kebab(preg_replace('/Widget$/', '', $class) ?? $class);
        $widgetType = $typePrefix . '.' . $typeSuffix;

        $stub = $this->files->get(__DIR__ . '/../Stubs/widget.stub');
        $contents = str_replace(
            ['{{ namespace }}', '{{ class }}', '{{ widgetType }}', '{{ widgetName }}', '{{ widgetDescription }}'],
            [$namespace, $class, $widgetType, Str::headline($typeSuffix), 'Custom widget for ' . $container],
            $stub,
        );

        $this->files->put($target, $contents);

        $this->info("Widget created: {$target}");
        $this->line("Type: {$widgetType}");
        $this->line('Implement resolve() and optionally configSchema(), then it appears in GET /api/v1/dashboard/widget-types.');

        return self::SUCCESS;
    }

    private function typePrefix(string $container): string
    {
        $section = explode('/', $container)[0] ?? 'custom';

        return match ($section) {
            'LifelogSection' => 'lifelog',
            'TaskManagerSection' => 'task-manager',
            'MusicSection' => 'music',
            'GallerySection' => 'gallery',
            'DashboardSection' => 'dashboard',
            default => 'custom',
        };
    }
}
