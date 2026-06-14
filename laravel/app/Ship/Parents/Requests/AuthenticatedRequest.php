<?php declare(strict_types=1);

namespace App\Ship\Parents\Requests;

use Illuminate\Foundation\Http\FormRequest;

abstract class AuthenticatedRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }
}
