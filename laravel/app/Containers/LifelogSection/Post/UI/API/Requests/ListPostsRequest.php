<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Post\UI\API\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ListPostsRequest extends FormRequest
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
            //
        ];
    }
}
