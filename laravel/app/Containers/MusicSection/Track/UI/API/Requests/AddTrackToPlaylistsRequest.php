<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Track\UI\API\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddTrackToPlaylistsRequest extends FormRequest
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
            'playlist_ids' => 'required|array',
            'playlist_ids.*' => 'required|numeric'
        ];
    }
}
