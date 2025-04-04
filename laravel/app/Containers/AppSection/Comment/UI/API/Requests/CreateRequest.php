<?php declare(strict_types=1);

namespace App\Containers\AppSection\Comment\UI\API\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'commentable_id' => 'required|numeric',
            'commentable_type' => 'required|string',
            'content' => 'required|string|max:1000'
        ];
    }
}
