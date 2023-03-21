<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Courses\CourseContentAttempt;
use App\Models\Forms\FormAnswer;
use App\Services\Access\AccessFactory;
use App\Transformers\Api\Forms\FormAnswerTransformer;
use Illuminate\Http\JsonResponse;
use Response;

class FormsController extends Controller
{

    /**
     * Returns the form answer of the user
     * @param int $foreignType
     * @param int $foreignId
     * @param FormAnswerTransformer $formAnswerTransformer
     * @return JsonResponse
     */
    public function getAnswerByRelatable(int $foreignType, int $foreignId, FormAnswerTransformer $formAnswerTransformer):JsonResponse {
        $user = user();

        $formAnswer = FormAnswer
            ::where('foreign_type', $foreignType)
            ->where('foreign_id', $foreignId)
            ->where('user_id', $user->id)
            ->with('fields.formField')
            ->first();

        $accessChecker = AccessFactory::getAccessChecker($formAnswer->relatable);
        if (!$accessChecker->hasAccess($user, $formAnswer->relatable)) {
            abort(403);
        }

        return Response::json($formAnswerTransformer->transform($formAnswer));
    }
}
