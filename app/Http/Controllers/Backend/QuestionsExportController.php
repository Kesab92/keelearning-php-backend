<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Imports\Excel\DefaultImport;
use App\Models\App;
use App\Services\QuestionSearch;
use App\Services\QuestionsExportEngine;
use Excel;
use Illuminate\Http\Request;
use Response;

class QuestionsExportController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth.backendaccess:questions,questions-edit');
    }

    public function export($from, $to, Request $request, QuestionsExportEngine $questionsExportEngine, QuestionSearch $questionSearch)
    {
        $app = App::find(appId());
        $appLanguages = $app->getLanguages();
        if (! in_array($from, $appLanguages)) {
            die($from.' is not a valid language for this app');
        }
        if (! in_array($to, $appLanguages)) {
            die($to.' is not a valid language for this app');
        }
        $filters = $request->input('selectedFilters');
        if($filters) {
            $filters = explode(',',$filters);
        } else {
            $filters = [];
        }
        $questions = $questionSearch
            ->find($app->id, $request->input('query'), $filters, $request->input('category'))
            ->with('questionAnswers')
            ->get();

        libxml_use_internal_errors(true);

        return Excel::download($questionsExportEngine->export($app, $from, $to, $questions), 'questions-from-'.$from.'-to-'.$to.'-'.date('Y-m-d-H:i:s').'.xlsx');
    }

    public function checkImport($importLanguage, Request $request, QuestionsExportEngine $questionsExportEngine)
    {
        $file = $request->file('file');
        if (! $file) {
            return Response::json([
                'success' => false,
                'error' => 'Keine gültige Datei ausgewählt.',
            ]);
        }
        try {
            $data = $questionsExportEngine->getImportData($file, appId());
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'error' => 'Möglicherweise verwenden Sie ein falsches Dateiformat für den Import. Das korrekte Dateiformat (.xlsx) erhalten Sie beim Export von Lernfragen.',
            ]);
        }
        if (! $data) {
            return Response::json([
                'success' => false,
                'error' => $questionsExportEngine->getLastError(),
            ]);
        }

        if (strtolower($data['to']) !== strtolower($importLanguage)) {
            return Response::json([
                'success' => false,
                'error' => 'Sie haben als Sprache "'.strtoupper($importLanguage).'" ausgewählt, die Datei enthält jedoch Daten zu "'.strtoupper($data['to']).'".',
            ]);
        }

        $entries = (new DefaultImport())->toArray($file)[0];
        $questionCount = array_reduce($entries, function ($carry, $entry) {
            if ($entry[1] === 'question:title') {
                return $carry + 1;
            }

            return $carry;
        }, 0);

        return Response::json([
            'success' => true,
            'questionCount' => $questionCount,
        ]);
    }

    public function import(Request $request, QuestionsExportEngine $questionsExportEngine)
    {
        $file = $request->file('file');
        if (! $file) {
            return Response::json([
                'success' => false,
                'error' => 'Keine gültige Datei ausgewählt.',
            ]);
        }
        $result = $questionsExportEngine->import($file, appId());
        if (! $result) {
            return Response::json([
                'success' => false,
                'error' => $questionsExportEngine->getLastError(),
            ]);
        }

        return Response::json([
            'success' => true,
        ]);
    }
}
