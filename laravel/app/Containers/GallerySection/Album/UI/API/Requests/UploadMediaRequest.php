<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Album\UI\API\Requests;

use App\Containers\GallerySection\Media\Rules\ValidateMediaExtension;
use Illuminate\Foundation\Http\FormRequest;

class UploadMediaRequest extends FormRequest
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
            'media_names' => 'required|array',
            'media_names.*' => ['required', 'string', new ValidateMediaExtension()],
            'media_folder' => 'required|string'
        ];
    }
}
