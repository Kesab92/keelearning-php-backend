<?php

namespace App\Http\Controllers\BackendApi;

use App\Http\Controllers\Controller;
use App\Jobs\ImportQuestions;
use App\Models\Category;
use App\Models\Import;
use Auth;
use Illuminate\Http\Request;
use Response;

class QuestionsImportController extends Controller
{
    private $appSettings;

    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth.backendaccess:questions,questions-edit');
        $this->middleware('auth.appsetting:import_questions');
    }

    /**
     * Imports questions.
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
        $questions = $data['questions'];
        $type = $data['type'];

        $import = new Import();
        $import->app_id = appId();
        $import->creator_id = Auth::user()->id;
        $import->type = Import::TYPE_QUESTIONS;
        $import->status = Import::STATUS_INPROGRESS;
        $import->steps = 5;
        $import->save();

        $error = null;
        $additionalData = [
            'category' => $category,
            'importId' => $import->id,
            'type'     => $type,
            'appId' => appId(),
            'creatorId' => Auth::user()->id,
        ];
        ImportQuestions::dispatch($additionalData, $headers, $questions);

        return Response::json([
            'importId' => $import->id,
        ]);
    }
}
