<?php

namespace App\Http\Requests\Music\Track;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePlaylistsRequest extends FormRequest
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
            'playlists' => 'required|array'
        ];
    }
}
