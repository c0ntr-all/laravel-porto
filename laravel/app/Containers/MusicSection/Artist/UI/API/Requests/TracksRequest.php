<?php

namespace App\Containers\MusicSection\Artist\UI\API\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TracksRequest extends FormRequest
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
            'filters' => 'sometimes|array',
            'filters.type' => 'sometimes|string',
            'filters.union' => 'sometimes|boolean',
            'filters.rate' => 'sometimes|array',
            'filters.rate.*' => 'sometimes|string'
        ];
    }
}
