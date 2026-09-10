<?php declare(strict_types=1);

namespace App\Containers\AppSection\CustomField\Data\Repositories;

use App\Containers\AppSection\CustomField\Data\DTO\CustomFieldCreateData;
use App\Containers\AppSection\CustomField\Data\DTO\CustomFieldUpdateData;
use App\Containers\AppSection\CustomField\Models\CustomField;
use App\Containers\AppSection\CustomField\Services\CustomFieldModuleRegistry;
use App\Containers\AppSection\CustomField\Tasks\ValidateFieldableOwnershipTask;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Exceptions\RepositoryException;
use App\Ship\Parents\QueryBuilder\QueryBuilder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Spatie\LaravelData\Optional;
use Spatie\QueryBuilder\AllowedFilter;

class CustomFieldRepository
{
    public function __construct(
        private readonly CustomFieldModuleRegistry $moduleRegistry,
        private readonly ValidateFieldableOwnershipTask $validateFieldableOwnershipTask,
    ) {
    }

    public function get(array $data): Collection
    {
        return QueryBuilder::for(CustomField::class)
            ->allowedSorts('position', 'created_at')
            ->allowedFilters([
                AllowedFilter::exact('fieldable_id'),
                AllowedFilter::callback('fieldable_type', function ($query, $value): void {
                    $query->where(
                        'fieldable_type',
                        ContainerAliasEnum::toCanonicalMorphAlias((string) $value),
                    );
                }),
                AllowedFilter::exact('type'),
            ])
            ->defaultSort('position', 'id')
            ->get();
    }

    /**
     * @throws RepositoryException
     */
    public function create(CustomFieldCreateData $dto): CustomField
    {
        $type = ContainerAliasEnum::toCanonicalMorphAlias($dto->fieldable_type);
        $model = $this->validateFieldableOwnershipTask->run(
            $type,
            (string) $dto->fieldable_id,
            $dto->user_id,
        );

        $payload = $this->moduleRegistry
            ->resolve($dto->type)
            ->validateAndNormalize($dto->payload);

        $position = $dto->position ?? $this->nextPosition($model);

        return $model->customFields()->create([
            'user_id' => $dto->user_id,
            'type' => $dto->type,
            'payload' => $payload,
            'position' => $position,
        ]);
    }

    public function update(CustomField $customField, CustomFieldUpdateData $dto): CustomField
    {
        $attributes = [];

        if (!($dto->payload instanceof Optional)) {
            $attributes['payload'] = $this->moduleRegistry
                ->resolve($customField->type)
                ->validateAndNormalize($dto->payload);
        }

        if (!($dto->position instanceof Optional)) {
            $attributes['position'] = $dto->position;
        }

        if ($attributes !== []) {
            $customField->update($attributes);
        }

        return $customField->refresh();
    }

    public function delete(CustomField $customField): bool
    {
        return (bool) $customField->delete();
    }

    private function nextPosition(Model $model): int
    {
        $max = (int) $model->customFields()->max('position');

        return $max + 1;
    }
}
