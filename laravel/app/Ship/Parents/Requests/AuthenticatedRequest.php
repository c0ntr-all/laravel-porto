<?php declare(strict_types=1);

namespace App\Ship\Parents\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;

abstract class AuthenticatedRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @param class-string<\Illuminate\Database\Eloquent\Model>|string $model
     */
    protected function existsForCurrentUser(string $model, string $column = 'id'): Exists
    {
        return Rule::exists($model, $column)->where('user_id', (int) $this->user()->id);
    }
}
