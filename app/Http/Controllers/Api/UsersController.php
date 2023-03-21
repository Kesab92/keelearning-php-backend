<?php

namespace App\Http\Controllers\Api;

use App\Http\APIError;
use App\Http\Controllers\Controller;
use App\Mail\Mailer;
use App\Models\Tag;
use App\Models\User;
use App\Services\GameEngine;
use App\Services\UserEngine;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Response;

class UsersController extends Controller
{
    /**
     * @var UserEngine
     */
    private $userEngine;
    /**
     * @var GameEngine
     */
    private $gameEngine;

    public function __construct(UserEngine $userEngine, GameEngine $gameEngine)
    {
        parent::__construct();
        $this->userEngine = $userEngine;
        $this->gameEngine = $gameEngine;
    }

    /**
     * The function handles the input query and returns a JSON with the ids and usernames of the fitting users whose
     * names or email-addresses match the query.
     *
     * @param Request $request
     * @return mixed
     */
    public function findUser(Request $request)
    {
        if ($request->has('q')) {
            $query = $request->get('q');
        } else {
            $query = $request->get('params')['q'];
        }
        $checkMaxGames = (bool) intval($request->get('checkMaxGames', 1));

        $users = $this->userEngine->findUsers($query);
        if ($checkMaxGames) {
            $maxGames = user()->app->maxConcurrentGames();
            $currentUser = user();
            $users->transform(function ($user) use ($maxGames, $currentUser) {
                if ($this->gameEngine->findActiveGamesBetweenUsers($currentUser->id, $user['id'])->count() >= $maxGames) {
                    $user['limitReached'] = true;
                    $user['maxGames'] = $maxGames;
                }

                return $user;
            });
        }

        return Response::json(array_values($users->toArray()));
    }

    /**
     * @return mixed
     */
    public function getRandomOpponent()
    {
        $randomUser = $this->userEngine->findRandomOpponent(user());
        if ($randomUser instanceof APIError) {
            return $randomUser;
        }

        return Response::json($randomUser);
    }

    public function tags()
    {
        return Response::json(user()->tags()->select('tags.id', 'label')->get()->toArray());
    }

    /**
     * Returns tags with group information by given app.
     * @return \Illuminate\Http\JsonResponse
     */
    public function tagsWithGroups()
    {
        $tags = Tag::whereIn('id', user()->tags->pluck('id'))
            ->with('tagGroup:id,show_highscore_tag')
            ->select(['id', 'label', 'tag_group_id'])
            ->get();

        return Response::json($tags);
    }

    /**
     * Returns tags with group information by given app.
     * @return \Illuminate\Http\JsonResponse
     */
    public function allTagsWithGroups()
    {
        $tags = Tag::where('app_id', user()->app_id)
            ->with('tagGroup:id,show_highscore_tag')
            ->select(['id', 'label', 'tag_group_id'])
            ->get();

        return Response::json($tags);
    }

    /**
     * The function returns all the categories accessible by the current user.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCategories()
    {
        return Response::json(user()->getQuestionCategories()->map(function ($c) {
            return [
                'id'   => $c->id,
                'name' => $c->name,
            ];
        }));
    }

    /**
     * The function returns all the necessary information of a user with the given id.
     *
     * @param             $userId
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUser($userId)
    {
        /** @var User $user */
        $user = User::ofSameApp()->find($userId);
        $data = collect([
            'id' => $user->id,
            'username' => $user->username,
            'email' => $user->getMailFrontend(),
            'avatar' => $user->avatar_url,
        ]);

        if (user()->is_admin || user()->id == $userId) {
            $data = $data->merge([
                'firstname' => $user->firstname,
                'lastname' => $user->lastname,
            ]);
        }

        return Response::json($data);
    }

    public function setLanguage(Request $request)
    {
        $lang = $request->get('lang');
        if (! in_array($lang, appLanguages())) {
            return Response::json([
                'success' => false,
            ]);
        }
        $user = user();
        $user->language = $lang;
        $user->save();

        return Response::json([
            'success' => true,
        ]);
    }

    /**
     *  Return possible Bots with id, difficulty, name and avatar.
     * @throws \Exception
     */
    public function getBots()
    {
        $bots = User::bot()
            ->where('app_id', user()->app_id)
            ->get();

        return Response::json($bots->transform(function ($bot) {
            /* @var User $bot */
            return [
                'id' => $bot->id,
                'username' => $bot->username,
                'displayname' => $bot->displayname,
                'difficulty' => $bot->is_bot,
                'avatar' => $bot->avatar_url,
            ];
        }));
    }

    /**
     * Changes users profile name.
     * @param int $userId
     * @param Request $request
     * @param Mailer $mailer
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(int $userId, Request $request, Mailer $mailer)
    {
        $this->validate($request, [
            'username' => 'nullable|min:2|max:255',
            'email' => 'nullable|email|min:3|max:255',
        ]);

        /** @var User $user */
        $user = User::where('app_id', user()->app_id)
            ->where('id', $userId)
            ->firstOrFail();

        if ($userId != user()->id) {
            app()->abort(403);
        }

        $basicFields = [
            'firstname',
            'lastname',
        ];

        foreach ($basicFields as $field) {
            if ($request->has($field)) {
                $value = $request->input($field, null);
                $user->setAttribute($field, $value);
            }
        }

        $appProfile = $user->getAppProfile();
        $needsPassword = $request->has('email') || $request->has('username');

        if ($needsPassword && !Hash::check($request->get('password'), $user->password)) {
            return new APIError(__('errors.password_wrong'));
        }

        if (
            $request->input('email') &&
            (
                $user->isMaillessAccount() ||
                $appProfile->getValue('allow_email_change')
            )
        ) {
            $email = utrim(mb_strtolower($request->input('email')));

            $mailValid = $user->app->isMailValid($email);
            if ($mailValid !== true) {
                return new APIError($mailValid, 400);
            }

            if (
                User
                    ::where('email', $email)
                    ->where('app_id', $user->app_id)
                    ->where('id', '!=', $user->id)
                    ->exists()
            ) {
                return new APIError(__('errors.mail_taken'), 401);
            }

            if(!$user->app->needsAccountActivation()) {
                $user->email = $email;
            } else {
                $mailer->sendEmailChangeConfirmation($user, $email);
            }
        }

        if (
            !$user->isTmpAccount() &&
            $appProfile->getValue('allow_username_change') &&
            $request->has('username')
        ) {
            $username = utrim($request->input('username'));
            if (
                $user->app->uniqueUsernames() &&
                User
                    ::where('username', $username)
                    ->where('app_id', $user->app_id)
                    ->where('id', '!=', $user->id)
                    ->exists()
            ) {
                return new APIError(__('errors.username_taken'), 400);
            }
            $user->username = $username;
        }

        $user->save();

        return Response::json([
            'success' => true,
        ]);
    }

    /**
     * Confirms email change.
     * @param int $userId
     * @param string $email
     * @param Request $request
     * @return APIError|Application|Factory|View
     */
    public function confirmEmailChange(int $userId, string $email, Request $request) {
        $request->merge(['email' => $request->route('email')]);
        $request->validate([
            'email' => 'required|email|min:3|max:255',
        ]);

        /** @var User $user */
        $user = User::where('id', $userId)
            ->firstOrFail();

        $appProfile = $user->getAppProfile();

        if (
            !$user->isMaillessAccount() &&
            !$appProfile->getValue('allow_email_change')
        ) {
            return view('app-message', [
                'appProfile' => $appProfile,
                'isError' => true,
                'message' => __('app_message.email_cant_be_changed'),
            ]);
        }

        $mailValid = $user->app->isMailValid($email);
        if ($mailValid !== true) {
            return view('app-message', [
                'appProfile' => $appProfile,
                'isError' => true,
                'message' => $mailValid,
            ]);
        }

        if (
            User
                ::where('email', $email)
                ->where('app_id', $user->app_id)
                ->where('id', '!=', $user->id)
                ->exists()
        ) {
            return view('app-message', [
                'appProfile' => $appProfile,
                'isError' => true,
                'message' => __('errors.mail_taken'),
            ]);
        }

        $user->email = $email;
        $user->save();

        return view('app-message', [
            'appProfile' => $appProfile,
            'message' => __('app_message.email_changed'),
        ]);
    }

    /**
     * Requests user deletion.
     * @param Mailer $mailer
     * @return JsonResponse
     */
    public function requestUserDeletion(Mailer $mailer):JsonResponse {
        $mailer->sendUserDeletionRequest(user());

        return Response::json([
            'success' => true,
        ]);
    }
}
