<?php declare(strict_types=1);

namespace App\Containers\AppSection\Registration\UI\API\Requests;

use App\Ship\Parents\Requests\PublicRequest;

class RegisterRequest extends PublicRequest
{
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
