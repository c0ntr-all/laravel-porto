<?php declare(strict_types=1);

namespace App\Containers\DashboardSection\Widget\UI\API\Requests;

use App\Containers\DashboardSection\Widget\Enums\WidgetSizeEnum;
use App\Containers\DashboardSection\Widget\Managers\WidgetRegistry;
use App\Ship\Parents\Requests\AuthenticatedRequest;
use Illuminate\Validation\Rule;

class CreateRequest extends AuthenticatedRequest
{
    public function rules(): array
    {
        /** @var WidgetRegistry $registry */
        $registry = app(WidgetRegistry::class);

        return [
            'type' => ['required', 'string', Rule::in($registry->types())],
            'title' => 'sometimes|nullable|string|max:120',
            'size' => ['sometimes', Rule::enum(WidgetSizeEnum::class)],
            'config' => 'sometimes|array',
            'is_enabled' => 'sometimes|boolean',
        ];
    }
}
