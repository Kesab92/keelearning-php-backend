<?php

namespace App\Http\Controllers\BackendApi;

use App\Http\Controllers\Controller;
use App\Http\Requests\BackendApi\Question\QuestionUpdateRequest;
use App\Jobs\QuestionsRemove;
use App\Models\AccessLog;
use App\Models\App;
use App\Models\Category;
use App\Models\CategoryHider;
use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\QuestionAttachment;
use App\Services\AccessLogEngine;
use App\Services\AccessLogMeta\Questions\AccessLogQuestionCreate;
use App\Services\AccessLogMeta\Questions\AccessLogQuestionDelete;
use App\Services\AccessLogMeta\Questions\AccessLogQuestionUpdate;
use App\Services\AppSettings;
use App\Services\AzureVideo\AzureVideoEngine;
use App\Services\GameEngine;
use App\Services\QuestionSearch;
use App\Services\QuestionsEngine;
use App\Services\TranslationEngine;
use Auth;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Response;
use Sopamo\LaravelFilepond\Filepond;
use Illuminate\Http\File;
use Storage;

class QuestionsController extends Controller
{

    const ORDER_BY = [
        'id',
        'updated_at',
        'missing_translations',
        'visible',
    ];
    const PER_PAGE = [
        50,
        100,
        200,
    ];
    const PER_PAGE_SEARCH = [
        15,
        25,
        50,
        100,
    ];

    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth.backendaccess:questions,questions-edit')->except(['search']);
    }

    public function index(QuestionSearch $questionSearch, Request $request, TranslationEngine $translationEngine)
    {
        $app = App::find(appId());
        $orderBy = $request->input('sortBy', 'updated_at');
        if (! in_array($orderBy, self::ORDER_BY)) {
            $orderBy = self::ORDER_BY[0];
        }
        $orderDescending = $request->input('descending', 'true') === 'true';
        if ($orderBy === 'missing_translations') {
            // We flip this order_by around, because it's much easier to sort by the inverse
            $orderBy = 'existing_translations';
            $orderDescending = ! $orderDescending;
        }
        $page = (int) $request->input('page') ?? 1;
        $perPage = $request->input('rowsPerPage');
        if (! in_array($perPage, self::PER_PAGE)) {
            $perPage = self::PER_PAGE[0];
        }
        $questionsQuery = $questionSearch->find($app->id, $request->input('query'), $request->input('selectedFilters'), $request->input('category'));

        // We have to do some trickery here, because the questionsQuery uses a group by, so we have to to a subquery here
        $questionCount = DB::table(DB::raw("({$questionsQuery->toSql()}) as questions"))
            ->setBindings($questionsQuery->getBindings())
            ->selectRaw('COUNT(*) AS count')
            ->first();
        $questionCount = $questionCount->count;

        $questions = $questionsQuery
            ->with('translationRelation')
            ->orderBy($orderBy, $orderDescending ? 'desc' : 'asc')
            ->offset($perPage * ($page - 1))
            ->limit($perPage)
            ->get();

        $missingTranslations = $questionSearch->getMissingTranslations($questions->pluck('id'), $app->id);

        $questions = array_map(function ($question) {
            unset($question['translation_relation']);

            return $question;
        }, $questions->values()->toArray());


        return Response::json([
            'count' => $questionCount,
            'questions' => $questions,
            'missingTranslations' => $missingTranslations,
            'warnings' => $this->getWarnings($request),
        ]);
    }

    /**
     * Returns the question using JSON
     *
     * @param int $questionId
     * @return JsonResponse
     * @throws \Exception
     */
    public function show(int $questionId):JsonResponse {
        $question = $this->getQuestion($questionId);

        $question->load([
            'questionAnswers',
            'questionAnswers.translationRelation',
            'attachments',
        ]);

        return Response::json($this->getQuestionResponse($question));
    }

    /**
     * Updates the question
     *
     * @param int $questionId
     * @param QuestionUpdateRequest $request
     * @param AccessLogEngine $accessLogEngine
     * @return JsonResponse
     * @throws \Throwable
     */
    public function update(int $questionId, QuestionUpdateRequest $request, AccessLogEngine $accessLogEngine):JsonResponse
    {
        DB::beginTransaction();

        $question = $this->getQuestion($questionId);
        $oldQuestion = AccessLogQuestionUpdate::createQuestionValues($question);
        $basicFields = ['title', 'category_id', 'answertime', 'visible'];
        foreach($basicFields as $field) {
            if($request->has($field)) {
                $value = $request->input($field, null);
                $question->setAttribute($field, $value);
            }
        }

        $question->save();

        if($request->has('question_answers')) {
            $questionAnswers = $request->input('question_answers', []);

            foreach ($questionAnswers as $questionAnswer) {
                if (empty($questionAnswer['content'])) {
                    if (empty($questionAnswer['id'])) {
                        // user added new answer fields in frontend without filling them
                        continue;
                    } else {
                        abort(403, 'Bestehende Antworten können nicht gelöscht werden.');
                    }
                }

                if (isset($questionAnswer['id'])) {
                    $answer = QuestionAnswer::findOrFail($questionAnswer['id']);

                    if ($answer->question_id !== $questionId) {
                        abort(403);
                    }
                } else {
                    $answer = new QuestionAnswer();
                    $question->questionAnswers()->save($answer);
                }


                $answer->content = $questionAnswer['content'];
                $answer->correct = $questionAnswer['correct'];

                if ($answer->correct) {
                    $answer->feedback = $questionAnswer['feedback'];
                } else {
                    $answer->feedback = null;
                }

                $answer->save();
            }
        }

        if($request->has('attachment')) {
            $attachment = $request->input('attachment', []);
            if($question->attachments->isNotEmpty()) {
                $questionAttachment = $question->attachments->first();
            } else {
                $questionAttachment = new QuestionAttachment();
                $questionAttachment->question_id = $question->id;
            }

            if ($attachment['type'] === 'youtube') {
                $questionAttachment->type = QuestionAttachment::ATTACHMENT_TYPE_YOUTUBE;
                $questionAttachment->attachment = $attachment['link'];

                $questionAttachment->save();

                $question->refresh();
            }
        }

        DB::commit();

        $accessLogQuestionUpdate = new AccessLogQuestionUpdate($question, $oldQuestion);

        if($accessLogQuestionUpdate->hasDifferences()) {
            $accessLogEngine->log(AccessLog::ACTION_QUESTION_UPDATE, $accessLogQuestionUpdate);
        }

        $question->load([
            'questionAnswers',
            'questionAnswers.translationRelation',
            'attachments',
        ]);

        return Response::json($this->getQuestionResponse($question));
    }

    private function getWarnings(Request $request)
    {
        $categoryId = $request->input('category');
        if(!$categoryId) {
            return [];
        }

        $category = Category
            ::where('app_id', appId())
            ->where('id', $categoryId)
            ->first();
        if(!$category) {
            return [];
        }

        $appSettings = new AppSettings($category->app_id);
        if(!$appSettings->getValue('module_quiz')) {
            return [];
        }

        if(!$category->isVisibleForScope(CategoryHider::SCOPE_QUIZ)) {
            return [];
        }

        /** @var GameEngine $gameEngine */
        $gameEngine = app(GameEngine::class);
        $questionsCount = $gameEngine->getCategoryQuestionsQuery($categoryId)->count();
        $warnings = [];
        $app = $appSettings->getApp();
        if($app->questions_per_round > $questionsCount) {
            $warnings[] = 'Derzeit ist die Kategorie "' . $category->name . '" noch nicht im Quiz-Battle spielbar. Benötigte Fragen: ' . $questionsCount . '/' . $app->questions_per_round;
        }
        return $warnings;
    }

    /**
     * Searches questions by title/category.
     *
     * @param Request $request
     *
     * @return \App\Http\APIError|JsonResponse
     * @throws \Exception
     */
    public function search(Request $request)
    {
        $searchTerm = utrim($request->input('query'));
        if (strlen($searchTerm) < 3) {
            app()->abort(422, 'Suchstring zu kurz.');
        }

        $orderBy = $request->input('sortBy', 'updated_at');
        if (! in_array($orderBy, self::ORDER_BY)) {
            $orderBy = self::ORDER_BY[0];
        }
        $orderDescending = $request->input('descending') === 'true';
        $page = (int) $request->input('page') ?? 1;
        $perPage = $request->input('rowsPerPage');
        if (! in_array($perPage, self::PER_PAGE_SEARCH)) {
            $perPage = self::PER_PAGE_SEARCH[0];
        }

        $questions = Question::visible();
        if (!$request->input('allow_learncards')) {
            $questions = $questions->withoutIndexCards();
        }
        $questionsQuery = $questions
            ->select(DB::raw('questions.id as id, question_translations.title as questiontitle, category_translations.name as category, categories.points as points, questions.type as type'))
            ->leftJoin('question_translations', 'question_translations.question_id', '=', 'questions.id')
            ->leftJoin('categories', 'questions.category_id', '=', 'categories.id')
            ->leftJoin('category_translations', 'category_translations.category_id', '=', 'categories.id')
            ->where('category_translations.language', language())
            ->where('question_translations.language', language())
            ->where('questions.app_id', appId())
            ->where(function ($query) use ($searchTerm) {
                $query->whereRaw('question_translations.title LIKE ?', '%'.escapeLikeInput($searchTerm).'%')
                    ->orWhereRaw('category_translations.name LIKE ?', '%'.escapeLikeInput($searchTerm).'%')
                    ->orWhere('questions.id', extractHashtagNumber($searchTerm));
            });

        $count = $questionsQuery->count();

        $questions = $questionsQuery
            ->orderBy($orderBy, $orderDescending ? 'desc' : 'asc')
            ->offset($perPage * ($page - 1))
            ->limit($perPage)
            ->get()
            ->map(function ($question) {
                return [
                    'id' => $question->id,
                    'title' => $question->title,
                    'category' => $question->category,
                    'points' => $question->points ?? 1,
                    'type' => $question->getTypeLabel(),
                ];
            })
            ->filter(function($entry) {
                return !!$entry['category'];
            });

        return Response::json([
            'count' => $count,
            'questions' => $questions,
            'success' => true,
        ]);
    }

    public function activateMultiple(Request $request, AccessLogEngine $accessLogEngine)
    {
        $questions = $request->input('questions');
        if (! is_array($questions)) {
            app()->abort(403);
        }

        DB::transaction(function () use ($accessLogEngine, $questions) {
            $questions = Question::whereIn('id', $questions)
                ->where('app_id', appId())
                ->where('visible', false)
                ->get();
            foreach ($questions as $question) {
                $oldQuestion = AccessLogQuestionUpdate::createQuestionValues($question);
                $question->visible = true;
                $question->save();

                $accessLogEngine->log(AccessLog::ACTION_QUESTION_UPDATE, new AccessLogQuestionUpdate($question, $oldQuestion));
            }
        });

        return Response::json([
            'success' => 1,
        ]);
    }

    public function deactivateMultiple(Request $request, AccessLogEngine $accessLogEngine)
    {
        $questions = $request->input('questions');
        if (! is_array($questions)) {
            app()->abort(403);
        }

        DB::transaction(function () use ($accessLogEngine, $questions) {
            $questions = Question::whereIn('id', $questions)
                ->where('app_id', appId())
                ->where('visible', true)
                ->get();
            foreach ($questions as $question) {
                $oldQuestion = AccessLogQuestionUpdate::createQuestionValues($question);
                $question->visible = false;
                $question->save();

                $accessLogEngine->log(AccessLog::ACTION_QUESTION_UPDATE, new AccessLogQuestionUpdate($question, $oldQuestion));
            }
        });

        return Response::json([
            'success' => 1,
        ]);
    }

    public function deleteMultipleInformation(Request $request)
    {
        $questionIds = $request->input('questions');
        if (! is_array($questionIds)) {
            app()->abort(403);
        }

        $result = [
            'games' => 0,
            'questionAnswers' => 0,
            'trainingAnswers' => 0,
            'questionAttachments' => 0,
        ];

        $questions = Question::whereIn('id', $questionIds)
            ->where('app_id', appId())
            ->get();

        $cantDeleteQuestions = [];
        foreach ($questions as $question) {
            if (! $question->canBeDeleted()) {
                $cantDeleteQuestions[$question->title] = $question->getBlockingDependees();
                continue;
            }
            if ($question->app_id != appId()) {
                app()->abort(403);
            }

            $tmp = $question->safeRemoveDependees();
            $result['games'] += $tmp['games'];
            $result['questionAnswers'] += $tmp['questionAnswers'];
            $result['trainingAnswers'] += $tmp['trainingAnswers'];
            $result['questionAttachments'] += $tmp['questionAttachments'];
        }

        if (count($cantDeleteQuestions) > 0) {
            return \Response::json([
                'errors' => $cantDeleteQuestions,
            ]);
        }

        return \Response::json($result);
    }

    public function deleteMultiple(Request $request)
    {
        $cantDeleteQuestions = [];
        $questionsQuery = Question::whereIn('id', $request->input('questions'));
        foreach ($questionsQuery->get() as $question) {
            if (! $question->canBeDeleted()) {
                $cantDeleteQuestions[$question->title] = $question->getBlockingDependees();
                continue;
            }
        }

        if (count($cantDeleteQuestions) > 0) {
            return \Response::json([
                'errors' => $cantDeleteQuestions,
            ]);
        }

        $questionsQuery->update([
            'visible' => false,
        ]);

        QuestionsRemove::dispatch($request->input('questions'), appId(), Auth::user()->id);

        return \Response::json([
            'success' => true,
        ]);
    }

    /**
     * @param int $questionId
     * @return JsonResponse
     */
    public function deleteInformation(int $questionId):JsonResponse
    {
        $question = $this->getQuestion($questionId);
        return Response::json([
            'dependencies' => $question->safeRemoveDependees(),
            'blockers' => $question->getBlockingDependees(),
        ]);
    }

    /**
     * @param int $questionId
     * @param AccessLogEngine $accessLogEngine
     * @return JsonResponse
     * @throws \Throwable
     */
    public function delete(int $questionId, AccessLogEngine $accessLogEngine):JsonResponse
    {
        $question = $this->getQuestion($questionId);

        $result = DB::transaction(function() use ($accessLogEngine, $question) {
            $accessLogEngine->log(AccessLog::ACTION_QUESTION_DELETE, new AccessLogQuestionDelete($question), Auth::user()->id);
            return $question->safeRemove();
        });

        if($result->isSuccessful()) {
            return Response::json([], 204);
        } else {
            return Response::json($result->getMessages(), 400);
        }
    }

    /**
     * Sets the media for a question.
     *
     * @param int $questionId
     * @param Request $request
     * @param AzureVideoEngine $azureVideoEngine
     * @param QuestionsEngine $questionsEngine
     * @param Filepond $filepond
     */
    public function uploadAttachment(int $questionId, Request $request, AzureVideoEngine $azureVideoEngine, QuestionsEngine $questionsEngine, Filepond $filepond)
    {
        set_time_limit(0);
        $question = $this->getQuestion($questionId);

        $filePath = $filepond->getPathFromServerId($request->input('serverId'));
        $disk = config('filepond.temporary_files_disk');
        $readStream = Storage::disk($disk)->readStream($filePath);
        $filename = $request->input('filename');
        $mimeType = $request->input('fileType');
        $extension = $request->input('fileExtension');

        if (! QuestionAttachment::isValidFileType($filePath, $mimeType, $extension) && ! $azureVideoEngine->isAVideo($mimeType)) {
            app()->abort(400);
        }

        if($question->attachments->isNotEmpty()) {
            $questionsEngine->removeMedia($question->attachments->first());
        }

        $questionAttachment = new QuestionAttachment();
        $questionAttachment->question_id = $questionId;

        if ($azureVideoEngine->isAVideo($mimeType)) {
            $tmpfname = tempnam("/tmp", uniqid('video'));
            file_put_contents($tmpfname, $readStream);
            Storage::disk($disk)->delete($filePath);
            $tmpFile = new File($tmpfname);
            $azureVideo = $azureVideoEngine->uploadVideo($question->app_id, $tmpFile);

            $questionAttachment->type = QuestionAttachment::ATTACHMENT_TYPE_AZURE_VIDEO;
            $questionAttachment->attachment = $azureVideo->id;
            $questionAttachment->attachment_url = '';
        } else {
            $newLocation = 'uploads/' . createFilenameFromString($filename);
            $readStream = Storage::disk($disk)->readStream($filePath);
            Storage::disk($disk)->writeStream($newLocation, $readStream, [
                'mimetype' => $mimeType,
            ]);

            if(isImage($mimeType)) {
                $questionAttachment->type = QuestionAttachment::ATTACHMENT_TYPE_IMAGE;
            }
            if(isAudio($mimeType)) {
                $questionAttachment->type = QuestionAttachment::ATTACHMENT_TYPE_AUDIO;
            }

            $questionAttachment->attachment = $newLocation;
            $questionAttachment->attachment_url = Storage::url($newLocation);
        }

        $questionAttachment->save();
    }

    /**
     * Deletes the attachment of the question.
     *
     * @param int $questionId
     * @param QuestionsEngine $questionsEngine
     * @return JsonResponse
     */
    public function deleteAttachment(int $questionId, QuestionsEngine $questionsEngine):JsonResponse {
        $question = $this->getQuestion($questionId);

        if($question->attachments->isNotEmpty()) {
            $questionsEngine->removeMedia($question->attachments->first());
            $question->refresh();
        }

        return Response::json($this->getQuestionResponse($question));
    }

    public function create(Request $request, AccessLogEngine $accessLogEngine)
    {
        $this->validate($request, [
            'category' => 'required|exists:categories,id',
            'title' => 'required',
        ]);

        /** @var App $app */
        $app = App::find(appId());

        $request->merge([
            'lang' => $app->getLanguage(),
        ]);

        $question = DB::transaction(function() use ($request, $accessLogEngine) {
            $question = new Question();
            // New questions are invisible by default
            $question->visible = false;
            $question->app_id = appId();
            $question->title = $request->input('title');
            $question->type = $request->input('type');
            $question->category_id = $request->input('category');
            $question->creator_user_id = Auth::user()->id;
            $question->save();

            // Create empty answers for the question

            switch ($question->type) {
                case Question::TYPE_BOOLEAN:
                    $question->addAnswer('Richtig', true);
                    $question->addAnswer('Falsch', false);
                    break;
                case Question::TYPE_INDEX_CARD:
                    $question->addAnswer('Rückseite', true);
                    break;
                default:
                    $question->addAnswer('Hier richtige Antwort eingeben', true);
                    $question->addAnswer('Hier falsche Antwort eingeben', false);
                    break;
            }

            $accessLogEngine->log(AccessLog::ACTION_QUESTION_CREATE, new AccessLogQuestionCreate($question));

            return $question;
        });

        return Response::json([
            'id' => $question->id,
        ]);
    }

    /**
     * Returns the question
     *
     * @param int $questionId
     * @return Question \Illuminate\Database\Eloquent\Model\Question
     * @throws \Exception
     */
    private function getQuestion(int $questionId):Question
    {
        $question = Question::findOrFail($questionId);

        // Check the access rights
        if ($question->app_id != appId()) {
            app()->abort(403);
        }
        return $question;
    }

    /**
     * Returns the question for the response
     *
     * @param Question $question
     * @return Question[]
     * @throws \Exception
     */
    private function getQuestionResponse(Question $question):array {
        $questionSearch = app(QuestionSearch::class);

        $missingTranslations = $questionSearch->getMissingTranslations([$question->id], $question->app_id);

        $question->translations = $question->allTranslationRelations->whereIn('language', [defaultAppLanguage(), language()])->values();
        $question->unsetRelation('allTranslationRelations');

        foreach($question->questionAnswers as $answer) {
            $answer->translations = $answer->allTranslationRelations->whereIn('language', [defaultAppLanguage(), language()])->values();
        }

        $difficulty = $question->getDifficulty(true);
        $isReusableClone = $question->isreusableclone;
        $question = $question->toArray();

        $question['difficulty'] = $difficulty;
        $question['isreusableclone'] = $isReusableClone;
        $question['missingTranslations'] = !empty($missingTranslations) ? reset($missingTranslations) : [];

        foreach($question['question_answers'] as &$answer) {
            unset($answer['translation_relation']);
            unset($answer['question']);
        }

        return [
            'question' => $question,
        ];
    }
}
