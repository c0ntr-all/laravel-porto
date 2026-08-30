<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Task\UI\API\Requests;

use App\Ship\Parents\Requests\AuthenticatedRequest;
use Illuminate\Support\Carbon;

class UpdateRequest extends AuthenticatedRequest
{
    public function rules(): array
    {
        return [
            'task_list_id' => 'sometimes|nullable|exists:App\Containers\TaskManagerSection\TaskList\Models\TaskList,id',
            'title' => 'sometimes|string|max:70',
            'content' => 'sometimes|nullable|max:3000',
            'is_finished' => 'sometimes|boolean',
            'finished_at' => 'sometimes|nullable|date_format:Y-m-d H:i:s',
            'is_declined' => 'sometimes|boolean',
        ];
    }

    protected function prepareForValidation(): void
    {
        if (!$this->exists('is_finished')) {
            return;
        }

        $this->merge([
            'finished_at' => $this->boolean('is_finished') ? Carbon::now()->format('Y-m-d H:i:s') : null,
        ]);
    }

    public function validated($key = null, $default = null): mixed
    {
        $validated = parent::validated();
        unset($validated['is_finished']);

        if ($key !== null) {
            return data_get($validated, $key, $default);
        }

        return $validated;
    }
}
