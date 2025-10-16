<?php declare(strict_types=1);

namespace App\Ship\Helpers;

use App\Ship\Traits\Makeable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;
use phpDocumentor\Reflection\Exception;

class ImageUpload
{
    use Makeable;

    private string $folder;
    private string $diskName;
    private string $filename;

    public function __construct($filename = '404.jpg', string $folder = 'unsorted', string $diskName = 'public')
    {
        $this->setDiskName($diskName)
             ->setFolder($folder)
             ->setFilename($filename);
    }

    /**
     * @param string $folder
     * @return $this
     */
    public function setFolder(string $folder): self
    {
        $this->folder = $folder;

        return $this;
    }

    /**
     * @param string $diskName
     * @return $this
     */
    public function setDiskName(string $diskName): self
    {
        $this->diskName = $diskName;

        return $this;
    }

    /**
     * @param string $filename
     * @return $this
     */
    public function setFilename(string $filename): self
    {
        $this->filename = $filename;

        return $this;
    }

    /**
     * Saves the image obtained from the form to Storage
     *
     * @param $image
     * @return string
     * @throws Exception
     */
    public function upload($image): string
    {
        $pathToReturn = $this->folder . '/' . $this->filename;
        $pathToSave = Storage::path('public') . '/' . $pathToReturn;

        try {
            Image::read($image)->save($pathToSave);
        } catch (Exception $e) {
            throw new Exception('Unable to save image: ' . $pathToSave . '. Because: ' . $e->getMEssage());
        }

        return $pathToReturn;
    }

    /**
     * Saves the image obtained from the URL to Storage
     *
     * @param string $url
     * @return false|string
     * @throws ConnectionException
     */
    public function uploadFromUrl(string $url): bool|string
    {
        $path = $this->folder . '/' . $this->filename;

        $image = Http::withHeaders([
            'User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.183 Safari/537.36',
        ])->withOptions([
            'verify' => false,
        ])->get($url)->body();

        $isSaved = Storage::disk($this->diskName)->put($path, $image);

        return $isSaved ? $path : false;
    }
}
