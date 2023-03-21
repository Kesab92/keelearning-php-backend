<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Storage;

class ImageUploader
{
    /**
     * Allowed mime types to upload images.
     */
    const ALLOWED_MIME_TYPES = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/svg+xml',
    ];

    const ALLOWED_FILE_EXTENSIONS = [
        'mp3',
        'png',
        'gif',
        'jpg',
        'jpeg',
        'svg',
    ];

    /**
     * Validates if the file is one of the given mimetypes and fileextensions.
     * @param $file
     * @return bool
     */
    public function validate($file)
    {
        if (! in_array(strtolower($file->getClientOriginalExtension()), self::ALLOWED_FILE_EXTENSIONS)) {
            return false;
        }

        if (strtolower($file->getClientOriginalExtension()) !== 'mp3'
            && ! in_array(strtolower(mime_content_type($file->getPathname())), self::ALLOWED_MIME_TYPES)) {
            return false;
        }

        return true;
    }

    /**
     * Uploads the file and returns the path to the image.
     * @param \Illuminate\Http\UploadedFile $file
     * @param string $folder
     * @return string
     */
    public function upload(\Illuminate\Http\UploadedFile $file, $folder = 'uploads')
    {
        return Storage::putFileAs($folder, $file, createFilename($file));
    }

    /**
     * Removes the cover image.
     * @param Model $model
     */
    public function removeCoverImage(Model $model)
    {
        if ($model->cover_image) {
            Storage::delete($model->cover_image);
            $model->cover_image = null;
            $model->cover_image_url = null;
            $model->save();
        }
    }
}
