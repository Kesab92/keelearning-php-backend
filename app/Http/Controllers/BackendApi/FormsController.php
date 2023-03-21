<?php

namespace App\Http\Controllers\BackendApi;

use App\Http\Controllers\Controller;
use App\Http\Requests\BackendApi\Form\FormStoreRequest;
use App\Http\Requests\BackendApi\Form\FormUpdateRequest;
use App\Models\AccessLog;
use App\Models\ContentCategories\ContentCategory;
use App\Models\Forms\Form;
use App\Services\AccessLogEngine;
use App\Services\AccessLogMeta\Forms\AccessLogFormCreate;
use App\Services\AccessLogMeta\Forms\AccessLogFormDelete;
use App\Services\AccessLogMeta\Forms\AccessLogFormUpdate;
use App\Services\Forms\FormEngine;
use App\Services\ImageUploader;
use App\Transformers\BackendApi\Forms\SimpleFormTransformer;
use App\Transformers\BackendApi\Forms\FormListTransformer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class FormsController extends Controller
{
    const ORDER_BY = [
        'id',
        'title',
    ];
    const PER_PAGE = [
        50,
        100,
        200,
    ];

    private FormEngine $formEngine;

    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth.backendaccess:forms,forms-edit')->except(['availableForms','getAll']);
        $this->middleware('auth.backendaccess:,forms-edit|courses-edit|courses-view')->only('getAll');



        $this->formEngine = app(FormEngine::class);
    }

    /**
     * Returns forms data
     *
     * @param Request $request
     * @param FormEngine $formEngine
     * @param FormListTransformer $formListTransformer
     * @return JsonResponse
     * @throws \Exception
     */
    public function index(Request $request, FormEngine $formEngine, FormListTransformer $formListTransformer):JsonResponse
    {
        $orderBy = $request->input('sortBy');
        if (! in_array($orderBy, self::ORDER_BY)) {
            $orderBy = self::ORDER_BY[0];
        }
        $orderDescending = $request->input('descending') === 'true';
        $page = (int) $request->input('page') ?? 1;
        $perPage = $request->input('rowsPerPage');
        if (! in_array($perPage, self::PER_PAGE)) {
            $perPage = self::PER_PAGE[0];
        }
        $filter = $request->input('filter');
        $search = utrim($request->input('search'));
        $tags = $request->input('tags', []);
        $categories = $request->input('categories', []);

        $formQuery = $formEngine->formsFilterQuery(appId(), Auth::user(), $search, $tags, $categories, $filter, $orderBy, $orderDescending);

        $count = $formQuery->count();
        $forms = $formQuery
            ->with([
                'categories',
                'tags',
                'translationRelation',
            ])
            ->offset($perPage * ($page - 1))
            ->limit($perPage)
            ->get();

        return response()->json([
            'count' => $count,
            'forms' => $formListTransformer->transformAll($forms),
        ]);
    }

    /**
     * Adds the form
     *
     * @param FormStoreRequest $request
     * @param AccessLogEngine $accessLogEngine
     * @return JsonResponse
     * @throws \Throwable
     */
    public function store(FormStoreRequest $request, AccessLogEngine $accessLogEngine):JsonResponse
    {
        $form = DB::transaction(function() use ($accessLogEngine, $request) {
            $form = new Form();
            $form->app_id = appId();
            $form->setLanguage(defaultAppLanguage(appId()));
            $form->created_by_id = Auth::user()->id;
            $form->last_updated_by_id = Auth::user()->id;
            $form->title = $request->input('title');
            $form->save();
            $form->syncTags($request->input('tags', []));

            $accessLogEngine->log(AccessLog::ACTION_FORM_CREATE, new AccessLogFormCreate($form));

            return $form;
        });

        return response()->json([
            'form' => $this->formEngine->getFormResponse($form),
        ]);
    }

    /**
     * Returns the form using JSON
     *
     * @param int $formId
     * @return JsonResponse
     * @throws \Exception
     */
    public function show(int $formId):JsonResponse {
        $form = $this->formEngine->getForm($formId, appId(), Auth::user());

        return response()->json([
            'form' => $this->formEngine->getFormResponse($form),
        ]);
    }

    /**
     * Updates the cover image.
     *
     * @param int $formId
     * @param Request $request
     * @param ImageUploader $imageUploader
     * @return JsonResponse
     */
    public function cover(int $formId, Request $request, ImageUploader $imageUploader): JsonResponse
    {
        $this->formEngine->getForm($formId, appId(), Auth::user());

        $file = $request->file('file');
        if (! $imageUploader->validate($file)) {
            app()->abort(403);
        }

        if (! $imagePath = $imageUploader->upload($file, 'uploads/form-cover')) {
            app()->abort(400);
        }
        $imagePath = formatAssetURL($imagePath, '3.0.0');

        return response()->json([
            'image' => $imagePath,
        ]);
    }

    /**
     * Updates the form
     *
     * @param int $formId
     * @param FormUpdateRequest $request
     * @param AccessLogEngine $accessLogEngine
     * @param FormEngine $formEngine
     * @return JsonResponse
     */
    public function update(int $formId, FormUpdateRequest $request, AccessLogEngine $accessLogEngine, FormEngine $formEngine): JsonResponse
    {
        $form = DB::transaction(function() use ($formEngine, $accessLogEngine, $formId, $request) {
            $form = $this->formEngine->getForm($formId, appId(), Auth::user());
            $oldFormResponse = $this->formEngine->getFormResponse($form);

            $basicFields = [
                'cover_image_url',
                'is_draft',
                'title',
            ];

            foreach ($basicFields as $field) {
                if ($request->has($field)) {
                    $value = $request->input($field, null);
                    $form->setAttribute($field, $value);
                }
            }

            $form->last_updated_by_id = Auth::user()->id;

            $form->save();

            if($request->has('tags')) {
                $form->syncTags($request->input('tags', []), 'tags', true);
            }

            if($request->has('categories')) {
                $categories = $request->input('categories', []);
                if (!is_array($categories)) {
                    $categories = [$categories];
                }
                $form->categories()->syncWithPivotValues($categories, [
                    'type' => ContentCategory::TYPE_FORMS,
                ]);
            }

            if($request->has('fields')) {
                $updatedFields = $request->input('fields', []);

                foreach ($updatedFields as $updatedField) {
                    $field = $formEngine->getFormField($updatedField['id'], $formId);
                    $field->is_required = $updatedField['is_required'];
                    $field->position = $updatedField['position'];
                    $field->title = $updatedField['title'];
                    $field->save();
                }
            }

            $form->refresh();
            $newFormResponse = $this->formEngine->getFormResponse($form);
            $accessLogFormUpdate = new AccessLogFormUpdate($oldFormResponse, $newFormResponse, language());

            if($accessLogFormUpdate->hasDifferences()) {
                $accessLogEngine->log(AccessLog::ACTION_FORM_UPDATE, $accessLogFormUpdate);
            }

            return $newFormResponse;
        });

        return response()->json([
            'form' => $form,
        ]);
    }

    /**
     * Converts the form to a draft
     *
     * @param int $formId
     * @param AccessLogEngine $accessLogEngine
     * @return JsonResponse
     */
    public function convertToDraft(int $formId, AccessLogEngine $accessLogEngine): JsonResponse
    {
        $form = $this->formEngine->getForm($formId, appId(), Auth::user());
        $oldForm = $this->formEngine->getFormResponse($form);
        $form->is_draft = true;
        $form->last_updated_by_id = Auth::user()->id;
        $form->save();

        $newForm = $this->formEngine->getFormResponse($form);
        $accessLogEngine->log(AccessLog::ACTION_FORM_UPDATE, new AccessLogFormUpdate($oldForm, $newForm, language()));

        return response()->json([
            'form' => $newForm,
        ]);
    }

    /**
     * Archives the form
     *
     * @param int $formId
     * @param AccessLogEngine $accessLogEngine
     * @return JsonResponse
     */
    public function archive(int $formId, AccessLogEngine $accessLogEngine): JsonResponse
    {
        $form = $this->formEngine->getForm($formId, appId(), Auth::user());
        $oldForm = $this->formEngine->getFormResponse($form);
        $form->is_archived = true;
        $form->last_updated_by_id = Auth::user()->id;
        $form->save();

        $newForm = $this->formEngine->getFormResponse($form);
        $accessLogEngine->log(AccessLog::ACTION_FORM_UPDATE, new AccessLogFormUpdate($oldForm, $newForm, language()));

        return response()->json([
            'form' => $newForm,
        ]);
    }

    /**
     * Unarchives the form
     *
     * @param int $formId
     * @param AccessLogEngine $accessLogEngine
     * @return JsonResponse
     */
    public function unarchive(int $formId, AccessLogEngine $accessLogEngine): JsonResponse
    {
        $form = $this->formEngine->getForm($formId, appId(), Auth::user());
        $oldForm = $this->formEngine->getFormResponse($form);
        $form->is_archived = false;
        $form->last_updated_by_id = Auth::user()->id;
        $form->save();

        $newForm = $this->formEngine->getFormResponse($form);
        $accessLogEngine->log(AccessLog::ACTION_FORM_UPDATE, new AccessLogFormUpdate($oldForm, $newForm, language()));

        return response()->json([
            'form' => $newForm,
        ]);
    }

    /**
     * @param int $formId
     * @return JsonResponse
     */
    public function deleteInformation(int $formId):JsonResponse
    {
        $form = $this->formEngine->getForm($formId, appId(), Auth::user());
        return response()->json([
            'dependencies' => $form->safeRemoveDependees(),
            'blockers' => $form->getBlockingDependees(),
        ]);
    }

    /**
     * @param int $formId
     * @param AccessLogEngine $accessLogEngine
     * @return JsonResponse
     */
    public function delete(int $formId, AccessLogEngine $accessLogEngine):JsonResponse
    {
        $form = $this->formEngine->getForm($formId, appId(), Auth::user());
        $form->load([
            'translationRelation',
        ]);

        $result = DB::transaction(function() use ($accessLogEngine, $form) {
            return $form->safeRemove();
        });

        if($result->isSuccessful()) {
            $accessLogEngine->log(AccessLog::ACTION_DELETE_FORM, new AccessLogFormDelete($form), Auth::user()->id);
            return response()->json([], 204);
        } else {
            return response()->json($result->getMessages(), 400);
        }
    }

    /**
     * Returns all forms for the active app.
     * @param SimpleFormTransformer $simpleFormTransformer
     * @return JsonResponse
     */
    public function getAll(SimpleFormTransformer $simpleFormTransformer): JsonResponse
    {
        $forms = Form
            ::where('app_id', appId())
            ->with([
                'translationRelation',
                'fields',
            ])
            ->get();

        return response()->json([
            'forms' => $simpleFormTransformer->transformAll($forms),
        ]);
    }
}
