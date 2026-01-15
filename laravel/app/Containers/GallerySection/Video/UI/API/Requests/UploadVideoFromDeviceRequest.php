<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Video\UI\API\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadVideoFromDeviceRequest extends FormRequest
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
            'file' => 'required|file|mimes:mp4,avi,mov,mkv,3gp|max:51200|nullable'
        ];
    }
}
