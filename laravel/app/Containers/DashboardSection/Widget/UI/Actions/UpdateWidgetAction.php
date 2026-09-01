<?php declare(strict_types=1);

namespace App\Containers\DashboardSection\Widget\UI\Actions;

use App\Containers\DashboardSection\Dashboard\Models\Dashboard;
use App\Containers\DashboardSection\Widget\Data\DTO\UpdateWidgetDto;
use App\Containers\DashboardSection\Widget\Exceptions\InvalidWidgetConfigException;
use App\Containers\DashboardSection\Widget\Managers\WidgetRegistry;
use App\Containers\DashboardSection\Widget\Models\Widget;
use App\Containers\DashboardSection\Widget\Tasks\AttachWidgetPayloadsTask;
use App\Containers\DashboardSection\Widget\Tasks\UpdateWidgetTask;
use App\Containers\DashboardSection\Widget\Tasks\ValidateWidgetConfigTask;
use App\Containers\DashboardSection\Widget\UI\API\Requests\UpdateRequest;
use App\Containers\DashboardSection\Widget\UI\API\Transformers\WidgetTransformer;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Spatie\LaravelData\Optional;

class UpdateWidgetAction extends BaseAction
{
    public function __construct(
        private readonly WidgetRegistry $widgetRegistry,
        private readonly ValidateWidgetConfigTask $validateWidgetConfigTask,
        private readonly UpdateWidgetTask $updateWidgetTask,
        private readonly AttachWidgetPayloadsTask $attachWidgetPayloadsTask,
    ) {
    }

    public function handle(Widget $widget, UpdateWidgetDto $dto): Widget
    {
        $definition = $this->widgetRegistry->get($widget->type);
        $size = $dto->size instanceof Optional ? $widget->size : $dto->size;

        if (!($dto->config instanceof Optional) || !($dto->size instanceof Optional)) {
            $config = $dto->config instanceof Optional
                ? ($widget->config ?? [])
                : $dto->config;

            try {
                $dto->config = $this->validateWidgetConfigTask->run($definition, $config, $size);
            } catch (InvalidWidgetConfigException $exception) {
                throw ValidationException::withMessages(['config' => $exception->getMessage()]);
            }
        }

        $widget = $this->updateWidgetTask->run($widget, $dto);
        $this->attachWidgetPayloadsTask->run(collect([$widget]));

        return $widget;
    }

    public function asController(Dashboard $dashboard, Widget $widget, UpdateRequest $request): JsonResponse
    {
        abort_unless($widget->dashboard_id === $dashboard->id, 404);

        $dto = UpdateWidgetDto::from($request->validated());
        $widget = $this->handle($widget, $dto);

        return fractal($widget, new WidgetTransformer())
            ->withResourceName('dashboard_widgets')
            ->addMeta(['message' => 'Widget successfully updated!'])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
