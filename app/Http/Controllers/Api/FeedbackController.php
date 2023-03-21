<?php

namespace App\Http\Controllers\Api;

use App\Http\APIError;
use App\Http\Controllers\Controller;
use App\Mail\Mailer;
use App\Models\AnalyticsEvent;
use App\Models\App;
use App\Models\AppRating;
use App\Models\Game;
use App\Models\LearningMaterial;
use App\Models\Question;
use App\Services\RatingEngine;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Response;

class FeedbackController extends Controller
{
    private $mailer;

    public function __construct(Mailer $mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * This function sends user feedback to the app owners.
     *
     * @param Request $request
     * @param RatingEngine $ratingEngine
     * @return APIError|\Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function sendFeedback(Request $request, RatingEngine $ratingEngine)
    {
        $user = user();

        if ($rating = $request->input('rating')) {
            if (! $ratingEngine->setRating($rating, $user->id)) {
                return new APIError(__('errors.rating_already_exists'));
            }
        }

        if ($request->input('message')) {
            if(!$request->input('type') || $request->input('type') === 'general') {
                $this->mailer->sendUserFeedback($user, $request->get('subject', ''), $request->get('message'));
            } else {
                $appId = null;
                switch ( $request->input('type') ) {
                    case 'question':
                        $questionItem = Question::find($request->get('id'));
                        $appId = $questionItem->app_id;
                        $url = url('/').'/questions#/questions/'.$request->get('id').'/general';
                        $type = 'Lernfragen';
                        break;
                    case 'learningmaterial':
                        $learningMaterialItem = LearningMaterial::find($request->get('id'));
                        $appId = $learningMaterialItem->app_id;
                        $url = url('/').'/learningmaterials?#/learningmaterials/'.$learningMaterialItem->learning_material_folder_id.'/'.$request->get('id').'/general';
                        $type = 'Mediathek';
                        break;
                    default:
                        $url = null;
                        $type = '';
                        break;
                }
                if($appId === null || $appId !== $user->app_id) {
                    throw new \Exception('Permissions denied');
                }
                $this->mailer->sendUserItemFeedback($user, $request->get('message'), $type, $url);
            }
        }

        AnalyticsEvent::log($user, AnalyticsEvent::TYPE_FEEDBACK_SENT);

        return Response::json(['success' => 1]);
    }

    /**
     * Checks the rating status. The rating status means if the user has rated the app already.
     */
    public function getRatingStatus()
    {
        $showWidgetRating = false;
        $rating = AppRating::where('user_id', user()->id);
        $showRating = $rating->count() == 0;
        if ($showRating) {
            $games = Game::where('player1_id', user()->id)
                ->orWhere('player2_id', user()->id)
                ->orderBy('created_at')
                ->get();

            if ($games->count() > 1) {
                $showWidgetRating = true;
            }

            if ($games->count() >= 5
                && Carbon::now() > $games->first()->created_at->addDays(7)
                && $games->filter(function ($game) {
                    return $game->status == Game::STATUS_FINISHED;
                })->count()) {
                $showWidgetRating = false;
            }
        }

        $ratingResponse = null;
        if ($model = $rating->first()) {
            $ratingResponse = [
                'created_at' => $model->created_at->toDateTimeString(),
                'value' => $model->rating,
            ];
        }

        return Response::json([
            'rating' => $ratingResponse,
            'showRating' => $showRating,
            'showWidgetRating' => $showWidgetRating,
        ]);
    }

    /**
     * Saves the rating for a user from the start up page of the app.
     * @param Request $request
     * @param RatingEngine $ratingEngine
     * @return APIError|\Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function createRating(Request $request, RatingEngine $ratingEngine)
    {
        $this->validate($request, [
            'rating' => 'required',
        ]);

        if (! $ratingEngine->setRating($request->input('rating'), user()->id)) {
            return new APIError(__('errors.rating_already_exists'));
        }

        return Response::json();
    }
}
