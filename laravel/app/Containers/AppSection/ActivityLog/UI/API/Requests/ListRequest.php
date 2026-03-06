<?php declare(strict_types=1);

namespace App\Containers\AppSection\ActivityLog\UI\API\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ListRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'filter.loggable_id' => 'required|string',
            'filter.loggable_type' => 'required|string',
        ];
    }
}
