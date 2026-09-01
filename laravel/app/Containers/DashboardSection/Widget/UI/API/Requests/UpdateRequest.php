<?php declare(strict_types=1);

namespace App\Containers\DashboardSection\Widget\UI\API\Requests;

use App\Containers\DashboardSection\Widget\Enums\WidgetSizeEnum;
use App\Ship\Parents\Requests\AuthenticatedRequest;
use Illuminate\Validation\Rule;

class UpdateRequest extends AuthenticatedRequest
{
    public function rules(): array
    {
        return [
            'title' => 'sometimes|nullable|string|max:120',
            'size' => ['sometimes', Rule::enum(WidgetSizeEnum::class)],
            'config' => 'sometimes|array',
            'sort_order' => 'sometimes|integer|min:0',
            'is_enabled' => 'sometimes|boolean',
        ];
    }
}
