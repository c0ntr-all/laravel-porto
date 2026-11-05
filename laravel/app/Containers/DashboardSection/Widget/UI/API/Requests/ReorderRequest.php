<?php declare(strict_types=1);

namespace App\Containers\DashboardSection\Widget\UI\API\Requests;

use App\Containers\DashboardSection\Dashboard\Models\Dashboard;
use App\Containers\DashboardSection\Widget\Models\Widget;
use App\Ship\Parents\Requests\AuthenticatedRequest;
use Illuminate\Validation\Rule;

class ReorderRequest extends AuthenticatedRequest
{
    public function rules(): array
    {
        $dashboard = $this->route('dashboard');
        $dashboardId = $dashboard instanceof Dashboard ? $dashboard->id : $dashboard;

        return [
            'ids' => 'required|array|min:1',
            'ids.*' => [
                'integer',
                'distinct',
                Rule::exists(Widget::class, 'id')->where(function ($query) use ($dashboardId): void {
                    $query->where('user_id', (int) $this->user()->id)
                        ->where('dashboard_id', $dashboardId);
                }),
            ],
        ];
    }
}
