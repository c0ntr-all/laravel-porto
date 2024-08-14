<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\TaskList\UI\API\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:30',
        ];
    }
}
