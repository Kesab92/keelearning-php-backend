<?php

namespace App\Http\Controllers\BackendApi;

use App\Http\Controllers\Controller;
use App\Http\Requests\BackendApi\Form\FormFieldStoreRequest;
use App\Models\AccessLog;
use App\Models\Forms\Form;
use App\Models\Forms\FormField;
use App\Services\AccessLogEngine;
use App\Services\AccessLogMeta\Forms\AccessLogFormDelete;
use App\Services\AccessLogMeta\Forms\AccessLogFormUpdate;
use App\Services\Forms\FormEngine;
use App\Transformers\BackendApi\Forms\FormFieldTransformer;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FormFieldsController extends Controller
{

    private FormEngine $formEngine;

    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth.backendaccess:forms,forms-edit');

        $this->formEngine = app(FormEngine::class);
    }

    /**
     * Adds the form
     *
     * @param int $formId
     * @param FormFieldStoreRequest $request
     * @param FormFieldTransformer $formFieldTransformer
     * @param AccessLogEngine $accessLogEngine
     * @return JsonResponse
     */
    public function store(int $formId, FormFieldStoreRequest $request, FormFieldTransformer $formFieldTransformer, AccessLogEngine $accessLogEngine):JsonResponse
    {
        $formField = DB::transaction(function() use ($formId, $accessLogEngine, $request) {
            $form = $this->formEngine->getForm($formId, appId(), Auth::user());
            $oldForm = $this->formEngine->getFormResponse($form);

            $formField = new FormField();
            $formField->form_id = $formId;
            $formField->setLanguage(defaultAppLanguage(appId()));
            $formField->position = $request->input('position');
            $formField->title = '';
            $formField->type = $request->input('type');
            $formField->save();

            $form->last_updated_by_id = Auth::user()->id;
            $form->updated_at = Carbon::now();
            $form->save();

            $newForm = $this->formEngine->getFormResponse($form);
            $accessLogEngine->log(AccessLog::ACTION_FORM_UPDATE, new AccessLogFormUpdate($oldForm, $newForm, language()));

            return $formField;
        });

        return response()->json($formFieldTransformer->transform($formField));
    }

    /**
     * @param int $formId
     * @param int $formFieldId
     * @return JsonResponse
     */
    public function deleteInformation(int $formId, int $formFieldId):JsonResponse
    {
        $this->formEngine->getForm($formId, appId(), Auth::user());
        $formField = $this->formEngine->getFormField($formFieldId, $formId);
        return response()->json([
            'dependencies' => $formField->safeRemoveDependees(),
            'blockers' => $formField->getBlockingDependees(),
        ]);
    }

    /**
     * @param int $formId
     * @param AccessLogEngine $accessLogEngine
     * @return JsonResponse
     */
    public function delete(int $formId, int $formFieldId, AccessLogEngine $accessLogEngine):JsonResponse
    {
        $form = $this->formEngine->getForm($formId, appId(), Auth::user());
        $oldForm = $this->formEngine->getFormResponse($form);
        $formField = $this->formEngine->getFormField($formFieldId, $formId);

        $result = DB::transaction(function() use ($accessLogEngine, $formField) {
            return $formField->safeRemove();
        });

        $form->last_updated_by_id = Auth::user()->id;
        $form->updated_at = Carbon::now();
        $form->save();

        $newForm = $this->formEngine->getFormResponse($form);

        if($result->isSuccessful()) {
            $accessLogEngine->log(AccessLog::ACTION_FORM_UPDATE, new AccessLogFormUpdate($oldForm, $newForm, language()));
            return response()->json([], 204);
        } else {
            return response()->json($result->getMessages(), 400);
        }
    }
}
