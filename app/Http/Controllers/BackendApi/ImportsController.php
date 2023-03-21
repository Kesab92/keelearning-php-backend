<?php

namespace App\Http\Controllers\BackendApi;

use App\Http\Controllers\Controller;
use App\Models\Import;
use Response;

class ImportsController extends Controller
{
    public function getLastImports()
    {
        $imports = Import
            ::with('creator')
            ->where('app_id', appId())
            ->orderBy('id', 'DESC')
            ->take(5)
            ->get()
            ->transform(function ($import) {
                $creator = $import->creator;
                if($creator) {
                    $creator = $creator->getDisplayNameBackend(false);
                } else {
                    $creator = '-';
                }
                return [
                    'id' => $import->id,
                    'type' => $import->type,
                    'created_at' => $import->created_at->format('Y-m-d H:i:s'),
                    'creator' => $creator,
                    'status' => $import->status,
                ];
            });

        return Response::json([
            'imports' => $imports,
        ]);
    }

    public function getImport($importId)
    {
        /** @var Import $import */
        $import = Import
            ::where('app_id', appId())
            ->where('id', $importId)
            ->firstOrFail();
        $import = [
            'progress' => $import->getProgress(),
            'steps' => $import->steps,
            'status' => $import->status,
        ];

        return Response::json([
            'import' => $import,
        ]);
    }
}
