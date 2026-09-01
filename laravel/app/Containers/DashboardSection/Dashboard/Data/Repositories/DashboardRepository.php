<?php declare(strict_types=1);

namespace App\Containers\DashboardSection\Dashboard\Data\Repositories;

use App\Containers\DashboardSection\Dashboard\Data\DTO\CreateDashboardDto;
use App\Containers\DashboardSection\Dashboard\Data\DTO\UpdateDashboardDto;
use App\Containers\DashboardSection\Dashboard\Models\Dashboard;
use Illuminate\Database\Eloquent\Collection;
use Spatie\LaravelData\Optional;

class DashboardRepository
{
    /**
     * @param list<string> $with
     */
    public function list(array $with = []): Collection
    {
        $query = Dashboard::query()->orderByDesc('is_default')->orderBy('sort_order')->orderBy('id');

        if ($with !== []) {
            $query->with($with);
        }

        return $query->get();
    }

    /**
     * @param list<string> $with
     */
    public function find(Dashboard $dashboard, array $with = []): Dashboard
    {
        if ($with !== []) {
            $dashboard->load($with);
        }

        return $dashboard;
    }

    public function create(CreateDashboardDto $dto): Dashboard
    {
        return Dashboard::create([
            'user_id' => $dto->user_id,
            'name' => $dto->name,
            'description' => $dto->description,
            'is_default' => $dto->is_default,
            'sort_order' => $dto->sort_order,
        ]);
    }

    public function update(Dashboard $dashboard, UpdateDashboardDto $dto): Dashboard
    {
        $attributes = [];

        foreach (['name', 'description', 'is_default', 'sort_order'] as $field) {
            if (!($dto->{$field} instanceof Optional)) {
                $attributes[$field] = $dto->{$field};
            }
        }

        if ($attributes !== []) {
            $dashboard->update($attributes);
        }

        return $dashboard->refresh();
    }

    public function delete(Dashboard $dashboard): bool
    {
        return (bool) $dashboard->delete();
    }

    public function clearDefaultExcept(?int $keepId, int $userId): void
    {
        $query = Dashboard::query()->where('user_id', $userId)->where('is_default', true);

        if ($keepId !== null) {
            $query->where('id', '!=', $keepId);
        }

        $query->update(['is_default' => false]);
    }
}
