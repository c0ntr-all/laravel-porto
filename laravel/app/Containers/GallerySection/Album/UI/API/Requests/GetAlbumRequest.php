<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Album\UI\API\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GetAlbumRequest extends FormRequest
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
