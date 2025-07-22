<?php declare(strict_types=1);

namespace App\Containers\AppSection\Registration\UI\API\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
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
            'name' => 'required|string|max:30',
            'email' => 'required|email|unique:App\Containers\AppSection\User\Models\User,email',
            'password' => 'required|string|min:6',
            'password_confirm' => 'required|string',
        ];
    }
}
