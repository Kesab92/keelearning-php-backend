<?php

namespace App\Imports;

use App\Models\QuestionAttachment;
use App\Services\ImageUploader;
use Exception;
use Illuminate\Http\UploadedFile;

trait QuestionAttachable
{
    /**
     * Imports the attachment
     *
     * @param int $questionId
     * @param array $questionData
     * @return void
     */
    public function importAttachment(int $questionId, array $questionData) {
        try {
            $imageUploader = app(ImageUploader::class);
            $imageURL = $this->getDataPoint($questionData, $this->headers, 'image');
            $imageName = substr($imageURL, strrpos($imageURL, '/') + 1);
            $fileData = file_get_contents($imageURL);

            if (!$fileData) {
                throw new Exception('File from the URL ' . $imageURL .'  does not exist');
            }

            $tempFile = tempnam(sys_get_temp_dir(), 'import-file-');
            file_put_contents($tempFile, $fileData);
            $file = new  UploadedFile($tempFile, $imageName);

            // Check if mime type and extension are one of the allowed ones
            if (!$imageUploader->validate($file)) {
                throw new Exception('File type is not allowed');
            }

            $relativePath = $imageUploader->upload($file);

            $attachment = new QuestionAttachment();
            $attachment->question_id = $questionId;
            $attachment->type = ($file->getClientOriginalExtension() == 'mp3' ?
                QuestionAttachment::ATTACHMENT_TYPE_AUDIO :
                QuestionAttachment::ATTACHMENT_TYPE_IMAGE);
            $attachment->attachment = $relativePath;
            $attachment->attachment_url = \Storage::url($relativePath);
            $attachment->save();
        } catch (Exception $e) {
            // The code avoids adding the attachment if the attachment is unavailable
        }
    }
}
