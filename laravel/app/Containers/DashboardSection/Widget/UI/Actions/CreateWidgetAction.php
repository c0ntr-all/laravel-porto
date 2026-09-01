<?php declare(strict_types=1);

namespace App\Containers\DashboardSection\Widget\UI\Actions;

use App\Containers\DashboardSection\Dashboard\Models\Dashboard;
use App\Containers\DashboardSection\Widget\Data\DTO\CreateWidgetDto;
use App\Containers\DashboardSection\Widget\Data\Repositories\WidgetRepository;
use App\Containers\DashboardSection\Widget\Exceptions\InvalidWidgetConfigException;
use App\Containers\DashboardSection\Widget\Managers\WidgetRegistry;
use App\Containers\DashboardSection\Widget\Models\Widget;
use App\Containers\DashboardSection\Widget\Tasks\AttachWidgetPayloadsTask;
use App\Containers\DashboardSection\Widget\Tasks\CreateWidgetTask;
use App\Containers\DashboardSection\Widget\Tasks\ValidateWidgetConfigTask;
use App\Containers\DashboardSection\Widget\UI\API\Requests\CreateRequest;
use App\Containers\DashboardSection\Widget\UI\API\Transformers\WidgetTransformer;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class CreateWidgetAction extends BaseAction
{
    public function __construct(
        private readonly WidgetRegistry $widgetRegistry,
        private readonly WidgetRepository $widgetRepository,
        private readonly ValidateWidgetConfigTask $validateWidgetConfigTask,
        private readonly CreateWidgetTask $createWidgetTask,
        private readonly AttachWidgetPayloadsTask $attachWidgetPayloadsTask,
    ) {
    }

    public function handle(Dashboard $dashboard, CreateWidgetDto $dto): Widget
    {
        $definition = $this->widgetRegistry->get($dto->type);

        try {
            $dto->config = $this->validateWidgetConfigTask->run($definition, $dto->config, $dto->size);
        } catch (InvalidWidgetConfigException $exception) {
            throw ValidationException::withMessages(['config' => $exception->getMessage()]);
        }

        $widget = $this->createWidgetTask->run($dto);
        $this->attachWidgetPayloadsTask->run(collect([$widget]));

        return $widget;
    }

    public function asController(Dashboard $dashboard, CreateRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $definition = $this->widgetRegistry->get($validated['type']);

        $dto = CreateWidgetDto::from([
            'dashboard_id' => $dashboard->id,
            'user_id' => (int) auth()->id(),
            'type' => $validated['type'],
            'title' => $validated['title'] ?? null,
            'size' => $validated['size'] ?? $definition->defaultSize()->value,
            'sort_order' => $this->widgetRepository->nextSortOrder($dashboard),
            'config' => $validated['config'] ?? [],
            'is_enabled' => $validated['is_enabled'] ?? true,
        ]);

        $widget = $this->handle($dashboard, $dto);

        return fractal($widget, new WidgetTransformer())
            ->withResourceName('dashboard_widgets')
            ->addMeta(['message' => 'Widget successfully created!'])
            ->respond(201, [], JSON_PRETTY_PRINT);
    }
}
