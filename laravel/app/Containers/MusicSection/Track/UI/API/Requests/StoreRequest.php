<?php declare(strict_types=1);

namespace App\Http\Requests\Music\Track;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'artist' => 'required|string',
            'name' => 'required|string',
            'link' => 'required|string'
        ];
    }
}
