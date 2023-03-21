<?php

namespace App\Http\Controllers\Backend;

use App\Exports\DefaultExport;
use App\Http\Controllers\Controller;
use App\Models\AccessLog;
use App\Models\App;
use App\Models\Category;
use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\QuestionAttachment;
use App\Services\AccessLogEngine;
use App\Services\AccessLogMeta\Questions\AccessLogQuestionAddAttachment;
use App\Services\AccessLogMeta\Questions\AccessLogQuestionDelete;
use App\Services\AccessLogMeta\Questions\AccessLogQuestionRemoveAttachment;
use App\Services\AccessLogMeta\Questions\AccessLogQuestionUpdate;
use App\Services\AppSettings;
use App\Services\AzureVideo\AzureVideoEngine;
use App\Services\ImageUploader;
use App\Services\TranslationEngine;
use Carbon\Carbon;
use Excel;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Request as Input;
use Session;
use Storage;
use View;

class QuestionsController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth.backendaccess:questions,questions-edit');
        View::share('activeNav', 'questions');
    }

    public function index(AppSettings $appSettings)
    {
        return view('vue-component', [
            'hasFluidContent' => false,
            'component' => 'routed',
            'props' => [
                'has_candy_frontend' => $appSettings->getValue('has_candy_frontend'),
            ],
        ]);
    }

    /**
     *  Exports all questions.
     * @param Request $request
     * @param TranslationEngine $translationengine
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     * @throws \Exception
     */
    public function export(Request $request, TranslationEngine $translationEngine)
    {
        $app = App::find(appId());

        $filename = 'question-export-'.Carbon::now()->format('d.m.Y-H:i:s').'.xlsx';

        $questions = $this->getQuestionsQuery($request)->get()->keyBy('id');

        $translationEngine->attachQuestionAnswers($questions, $app);
        $questions = $translationEngine->attachQuestionTranslations($questions, $app);

        $answerCount = 0;
        foreach ($questions as $question) {
            if ($question->answers) {
                $answerCount = max($answerCount, count($question->answers));
            }
        }
        $headers = [
            'questionId',
            'questionType',
            'questionTitle',
            'visible',
            'categoryName',
        ];
        $hasFeedback = [];
        $i = 0;
        while ($i < $answerCount) {
            $oneQuestionsHasFeedback = $questions->reduce(function ($carry, $question) use ($i) {
                $answers = array_values($question->answers ?? []);
                if (isset($answers[$i]) && $answers[$i]->feedback) {
                    return true;
                }

                return $carry;
            }, false);
            $hasFeedback[$i] = $oneQuestionsHasFeedback;
            $headers[] = 'answer'.($i + 1).'Id';
            $headers[] = 'answer'.($i + 1).'Content';
            if ($oneQuestionsHasFeedback) {
                $headers[] = 'answer'.($i + 1).'Feedback';
            }
            $headers[] = 'answer'.($i + 1).'Correct';
            $i++;
        }
        /** @var Collection $categories */
        $categories = Category::withTranslation()->get()->keyBy('id');
        $questions->transform(function ($question) use ($answerCount, $categories, $hasFeedback) {
            $answers = $question->answers ?? [];
            switch ($question->type) {
                case Question::TYPE_BOOLEAN:
                    $questionType = 'BOOLEAN';
                    break;
                case Question::TYPE_SINGLE_CHOICE:
                    $questionType = 'SINGLE_CHOICE';
                    break;
                case Question::TYPE_MULTIPLE_CHOICE:
                    $questionType = 'MULTIPLE_CHOICE';
                    break;
                case Question::TYPE_INDEX_CARD:
                    $questionType = 'INDEX_CARD';
                    break;
                default:
                    $questionType = '';
                    break;
            }
            $categoryLabel = '';
            if ($categories->has($question->category_id)) {
                $categoryLabel = $categories->get($question->category_id)->name;
            }
            $entry = [
                'id' => $question->id,
                'type' => $questionType,
                'title' => $question->title,
                'visible' => $question->visible,
                'category' => $categoryLabel,
            ];
            $i = 0;
            foreach ($answers as $answer) {
                $entry[] = $answer->id;
                $entry[] = $answer->content;
                if ($hasFeedback[$i]) {
                    $entry[] = $answer->feedback;
                }
                $entry[] = $answer->correct;
                $i++;
            }
            while ($i < $answerCount) {
                $entry[] = '';
                $entry[] = '';
                if ($hasFeedback[$i]) {
                    $entry[] = '';
                }
                $entry[] = '';
                $i++;
            }

            return $entry;
        });
        $data = [
            'questions' => $questions,
            'headers' => $headers,
        ];
        libxml_use_internal_errors(true);

        return Excel::download(new DefaultExport($data, 'questions.csv.export'), $filename);
    }
}
