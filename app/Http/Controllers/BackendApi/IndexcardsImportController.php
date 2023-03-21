<?php

namespace App\Http\Controllers\BackendApi;

use App\Http\Controllers\Controller;
use App\Jobs\ImportIndexCards;
use App\Models\Category;
use App\Models\Import;
use Auth;
use Illuminate\Http\Request;
use Response;

class IndexcardsImportController extends Controller
{
    private $appSettings;

    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth.backendaccess:index_cards,indexcards-edit');
        $this->middleware('auth.appsetting:import_index_cards');
    }

    /**
     * Imports index cards.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function import(Request $request)
    {
        $data = json_decode($request->get('data'), true);

        $category = Category::tagRights()->findOrFail($data['category']);
        if ($category->app_id !== appId()) {
            app()->abort(403);
        }
        $headers = $data['headers'];
        $indexcards = $data['indexcards'];

        $import = new Import();
        $import->app_id = appId();
        $import->creator_id = Auth::user()->id;
        $import->status = Import::STATUS_INPROGRESS;
        $import->steps = 1;
        $import->type = Import::TYPE_INDEXCARDS;
        $import->save();

        $additionalData = [
            'appId'     => appId(),
            'category'  => $category,
            'creatorId' => Auth::user()->id,
            'importId'  => $import->id,
        ];
        ImportIndexCards::dispatch($additionalData, $headers, $indexcards);

        return Response::json([
            'importId' => $import->id,
        ]);
    }
}
