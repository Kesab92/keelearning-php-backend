<?php

namespace App\Http\Controllers\BackendApi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Response;
use Storage;

class PageContentUploadController extends Controller
{
    /**
     * Uploads contents.
     * @param Request $request
     * @return
     */
    public function upload(Request $request)
    {
        $file = $request->file('file');
        if (! $file) {
            return Response::json([
                'success' => false,
                'error'   => 'Es wurde keine Datei hochgeladen.',
            ]);
        }

        $filePath = Storage::putFileAs('uploads', $file, createFilename($file));

        return Response::json([
            'data'    => Storage::url($filePath),
            'success' => true,
        ]);
    }
}
