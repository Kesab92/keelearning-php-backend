<?php

namespace App\Mail;

use App\Models\App;
use App\Models\Appointments\Appointment;
use App\Models\Comments\Comment;
use App\Models\Competition;
use App\Models\Courses\Course;
use App\Models\Courses\CourseParticipation;
use App\Models\Game;
use App\Models\KeelearningModel;
use App\Models\LearningMaterial;
use App\Models\News;
use App\Models\NotificationSubscription;
use App\Models\QuizTeam;
use App\Models\Reminder;
use App\Models\Reporting;
use App\Models\SuggestedQuestion;
use App\Models\Test;
use App\Models\TestSubmission;
use App\Models\User;
use App\Models\Webinar;
use App\Models\WebinarAdditionalUser;
use App\Push\Deepstream;
use App\Push\Notifier;
use App\Services\Courses\CoursesEngine;
use App\Services\MorphTypes;
use App\Services\NotificationSettingsEngine;
use Exception;
use Mail;

class Mailer
{
    protected $notificationSettingsEngine = null;

    public function __construct()
    {
        Mail::getSwiftMailer()
            ->registerPlugin(new \Swift_Plugins_AntiFloodPlugin(20));

        $this->notificationSettingsEngine = app(NotificationSettingsEngine::class);
    }

    /**
     * The method sends an email to player number 2 and tells him, that it's his turn now.
     *
     * @param $gameId
     */
    public function sendInvitation($gameId)
    {
        $game = Game::find($gameId);
        $app = $game->app;
        $player1 = User::find($game->player1_id);
        $player2 = User::find($game->player2_id);

        // If player 2 is active, send him an email
        if (!$player2->active || $player2->is_bot) {
            return;
        }

        $data = [
            'player1_name' => $player1->username,
            'player2_name' => $player2->username,
            'app' => $app,
            'game_id' => $gameId,
            'opponent_avatar' => $player1->avatar_url,
        ];

        /** @var Deepstream $deepstream */
        $deepstream = new Deepstream($app);
        $deepstream->sendEvent('users/' . $game->player2_id . '/gameinvitation', $data);

        $gameInvitation = new GameInvitation($player1, $player2, $game, $app);

        $gameInvitation->sendPushNotification();
        $gameInvitation->sendEmail();
    }

    /**
     * Send the user an email after registration.
     *
     * @param User $user
     */
    public function sendWelcomeMail(User $user)
    {
        $authWelcome = new AuthWelcome($user);
        $authWelcome->sendEmail();
    }

    /**
     * Send the user an email after SSO registration.
     *
     * @param User $user
     */
    public function sendSSOWelcomeMail(User $user)
    {
        $authSSOWelcome = new AuthSSOWelcome($user);
        $authSSOWelcome->sendEmail();
    }

    /**
     * Send the user an email with his new credentials.
     *
     * @param User $user
     * @param string $password
     */
    public function sendResetEmail(User $user, string $password)
    {
        $authResetPassword = new AuthResetPassword($password, $user);
        $authResetPassword->sendEmail();
    }

    /**
     * Send the user an email with his new credentials, linking to the backend.
     *
     * @param User $user
     * @param string $password
     */
    public function sendBackendResetEmail(User $user, string $password)
    {
        $authBackendResetPassword = new AuthBackendResetPassword($password, $user);
        $authBackendResetPassword->sendEmail();
    }

    /**
     * Send the admins an email when a new question has been suggested.
     *
     * @param User $user
     * @param SuggestedQuestion $question
     */
    public function sendSuggestedQuestionNotification(User $user, SuggestedQuestion $question)
    {
        $appQuestionSuggestion = new AppQuestionSuggestion($question, $user);
        $appQuestionSuggestion->sendEmail($user->app->getNotificationMails());
    }

    /**
     * Send the admins an email with user feedback.
     *
     * @param User $user
     * @param string $subject
     * @param string $message
     */
    public function sendUserFeedback(User $user, string $subject, string $message)
    {
        $appFeedback = new AppFeedback($subject, $message, $user);
        $appFeedback->sendEmail($user->app->getNotificationMails());
    }

    /**
     * Send the admins an email with user feedback.
     *
     * @param User $user
     * @param string $message
     * @param string $type
     * @param string $url
     */
    public function sendUserItemFeedback(User $user, string $message, string $type, string $url)
    {
        $itemFeedback = new ItemFeedback($message, $type, $url, $user);
        $itemFeedback->sendEmail($user->app->getNotificationMails());
    }

    /**
     * Send the admins an email with the user's request to access a course
     *
     * @param Course $course
     * @param User $user
     */
    public function sendCourseAccessRequest(Course $course, User $user)
    {
        $to = [];

        foreach ($course->managers as $manager) {
            if (!$manager->isMaillessAccount()) {
                $to[] = $manager->email;
            }
        }
        if (empty($to)) {
            $to = $user->app->getNotificationMails();
        }
        if (empty($to)) {
            return;
        }

        $courseAccessRequest = new CourseAccessRequest($course, $user);
        $courseAccessRequest->sendEmail($to);
    }

    /**
     * The function sends a reminder mail to the player whose turn is next.
     *
     * @param Game $game
     * @param User $player Currently active player that will be notified
     * @param User $opponent
     */
    public function sendRoundReminder(Game $game, User $player, User $opponent)
    {
        $app = $game->app;
        // The player is active and receives an email
        if (!$player->active || $player->is_bot) {
            return;
        }

        /** @var Deepstream $deepstream */
        $deepstream = new Deepstream($app);
        $deepstream->sendEvent('users/' . $player->id . '/gameupdate', [
            'game_id' => $game->id,
            'opponent_name' => $opponent->username,
            'opponent_avatar' => $opponent->avatar_url,
            'status' => $game->status,
        ]);

        $gameReminder = new GameReminder($game, $opponent, $player);
        $gameReminder->sendEmail();
        $gameReminder->sendPushNotification();

        $opponentGameReminder = new GameReminder($game, $player, $opponent);

        if ($opponentGameReminder->wantsPushNotification()) {
            $badgeCount = Notifier::getBadgeCount($opponent);
            Notifier::setBadgeCount($opponent, $badgeCount);
        }
    }

    /**
     * Sends a game finalize info
     * @param Game $game
     * @param User $player
     * @param User $opponent
     */
    public function sendGameFinalizeInfo(Game $game, User $player, User $opponent)
    {
        // The player is active and receives an email
        if (!$player->active || $player->is_bot) {
            return;
        }

        /** @var Deepstream $deepstream */
        $deepstream = new Deepstream($game->app);
        $deepstream->sendEvent('users/' . $player->id . '/gameupdate', [
            'game_id' => $game->id,
            'opponent_name' => $opponent->username,
            'opponent_avatar' => $opponent->avatar_url,
            'status' => $game->status,
        ]);

        if ($game->hasWon($player)) {
            $email = new GameWonInfo($game, $opponent, $player);
            $opponentEmail = new GameWonInfo($game, $player, $opponent);
        } elseif ($game->hasLost($player)) {
            $email = new GameLostInfo($game, $opponent, $player);
            $opponentEmail = new GameLostInfo($game, $player, $opponent);
        } else {
            $email = new GameDrawInfo($game, $opponent, $player);
            $opponentEmail = new GameDrawInfo($game, $player, $opponent);
        }

        $email->sendEmail();
        $email->sendPushNotification();

        if ($opponentEmail->wantsPushNotification()) {
            $badgeCount = Notifier::getBadgeCount($opponent);
            Notifier::setBadgeCount($opponent, $badgeCount);
        }
    }

    /**
     * The function sends an email to the person that should be invited to the app.
     *
     * @param int $appId
     * @param string $receiverEmail
     * @param int $userId
     * @param string $password
     */
    public function sendAppInvitation(int $appId, string $receiverEmail, int $userId, string $password)
    {
        $app = App::find($appId);
        $user = User::find($userId);

        $appInvitation = new AppInvitation($password, $user, $app);
        $appInvitation->sendEmail($receiverEmail);
    }

    /**
     * The function sends an email to the person that was added to a new quiz team.
     *
     * @param int $userId
     * @param QuizTeam $quizTeam
     */
    public function sendNewQuizTeamNotification(int $userId, QuizTeam $quizTeam)
    {
        $user = User::find($userId);

        $quizTeamAdd = new QuizTeamAdd($quizTeam, $user);
        $quizTeamAdd->sendEmail();
        $quizTeamAdd->sendPushNotification();
    }

    /**
     * The function sends an email with a quiz report.
     *
     * @param Reporting $reporting
     * @param string $recipientEmail
     * @param $tags
     * @param string $interval
     */
    public function sendQuizReporting(Reporting $reporting, string $recipientEmail, $tags, string $interval)
    {
        $quizReporting = new QuizReporting($reporting, $recipientEmail, $tags, $interval);
        $quizReporting->sendEmail($recipientEmail);
    }

    /**
     * The function sends an email with a user report.
     *
     * @param Reporting $reporting
     * @param string $recipientEmail
     * @param $tags
     * @param string $interval
     */
    public function sendUserReporting(Reporting $reporting, string $recipientEmail, $tags, string $interval)
    {
        $userReporting = new UserReporting($reporting, $recipientEmail, $tags, $interval);
        $userReporting->sendEmail($recipientEmail);
    }

    /**
     * The function sends an e-mail to a user to remind him/her about a running competition.
     *
     * @param User $user
     * @param Competition $competition
     * @param int $rankingNumber
     */
    public function sendCompetitionReminder(User $user, Competition $competition, int $rankingNumber)
    {
        if (!$user->active) {
            return;
        }
        if ($competition->getEndDate()->isPast()) {
            return;
        }

        $competitionReminder = new CompetitionReminder($rankingNumber, $competition, $user);
        $competitionReminder->sendEmail();
        $competitionReminder->sendPushNotification();
    }

    /**
     * The function sends an e-mail to a user to remind them they're gonna be deleted.
     *
     * @param User $user
     * @param int $deletionDays How long until deletion
     */
    public function sendExpirationReminder(User $user, int $deletionDays)
    {
        $expirationReminder = new ExpirationReminder($deletionDays, $user);
        $expirationReminder->sendEmail();
        $expirationReminder->sendPushNotification();
    }

    /**
     * The function sends an e-mail to a user to remind him/her to play.
     *
     * @param int $appId
     * @param int $userId
     * @param int $rankingNumber
     */
    public function sendAppReminder(int $appId, int $userId, int $rankingNumber)
    {
        $app = App::find($appId);
        $user = User::find($userId);

        $appReminder = new AppReminder($rankingNumber, $user, $app);
        $appReminder->sendEmail();
        $appReminder->sendPushNotification();
    }

    /**
     * Sends information to both users, that the game has been aborted.
     *
     * @param Game $game
     */
    public function sendGameAbortInformation(Game $game)
    {
        // Send the message for player 1, if active
        $firstPlayerGameAbort = new GameAbort($game, $game->player2, $game->player1);
        $firstPlayerGameAbort->sendEmail();
        $firstPlayerGameAbort->sendPushNotification();
        // Send the message to player 2, if active
        $secondPlayerGameAbort = new GameAbort($game, $game->player1, $game->player2);
        $secondPlayerGameAbort->sendEmail();
        $secondPlayerGameAbort->sendPushNotification();
    }

    /**
     * The function sends the competition results to the users given as an input. The data has to be of the following
     * format: $userData = [['userId' => $id, 'rightAnswers' => $rightAnswers], ...];.
     *
     * @param array $userData
     * @param Competition $competition
     */
    public function sendCompetitionResults(array $userData, Competition $competition)
    {
        $maxCount = count($userData);
        $app = $competition->app;

        // Create a unique message for every player and send it
        foreach ($userData as $index => $userInfo) {
            $player = User::find($userInfo['userId']);
            if (!$player) {
                continue;
            }

            $competitionResult = new CompetitionResult($index + 1, $maxCount, $userInfo['rightAnswers'], $competition, $player, $app);
            $competitionResult->sendEmail();
            $competitionResult->sendPushNotification();
        }
    }

    /**
     * The function sends an e-mail to a user to tell them they passed the test.
     * @param TestSubmission $submission
     */
    public function sendTestPassed(TestSubmission $submission)
    {
        /** @var User $user */
        $user = $submission->user;
        if (!$user->active || !is_null($user->deleted_at)) {
            return;
        }

        $hasCertificateTemplate = $submission->test->hasCertificateTemplate();
        if (!$hasCertificateTemplate) {
            return;
        }

        $testPassed = new TestPassed($submission, $user);
        $testPassed->sendEmail();
        $testPassed->sendPushNotification();

        if ($submission->test->send_certificate_to_admin) {
            $testPassedAdminNotification = new TestPassed($submission, $user);
            Mail::to($submission->test->app->getNotificationMails())->send($testPassedAdminNotification);
        }
    }

    /**
     * Sends news notification if any news is published.
     * @param News $news
     * @param User $user
     */
    public function sendNewsNotification(News $news, User $user)
    {
        $newsPublishedInfo = new NewsPublishedInfo($news, $user);
        $newsPublishedInfo->sendEmail();
        $newsPublishedInfo->sendPushNotification();
    }

    /**
     * Sends learning material notification if any learning material is published.
     * @param User $user
     * @param LearningMaterial $learningMaterial
     */
    public function sendLearningMaterialPublishedNotification(User $user, LearningMaterial $learningMaterial)
    {
        $learningMaterialsPublished = new LearningMaterialsPublished($learningMaterial, $user);
        $learningMaterialsPublished->sendEmail();
        $learningMaterialsPublished->sendPushNotification();
    }

    /**
     * Sends reminder of upcoming webinar to registered user.
     * @param User $user
     * @param Webinar $webinar
     */
    public function sendWebinarReminder(User $user, Webinar $webinar)
    {
        $webinarReminder = new WebinarReminder($webinar, $user);
        $webinarReminder->sendEmail();
        $webinarReminder->sendPushNotification();
    }

    /**
     * Sends reminder of upcoming webinar to unregistered participant.
     * @param WebinarAdditionalUser $webinarAdditionalUser
     */
    public function sendWebinarReminderExternal(WebinarAdditionalUser $webinarAdditionalUser)
    {
        $webinarReminderExternal = new WebinarReminderExternal($webinarAdditionalUser);
        $webinarReminderExternal->sendEmail($webinarAdditionalUser->email);
    }


    /**
     * Sends a competition invitation
     * @param User $user
     * @param Competition $competition
     */
    public function sendCompetitionInvite(User $user, Competition $competition)
    {
        $competitionInvitation = new CompetitionInvitation($competition, $user);
        $competitionInvitation->sendEmail();
        $competitionInvitation->sendPushNotification();
    }

    /**
     * Sends a test reminder to a user.
     * @param User $user
     * @param Test $test
     */
    public function sendTestReminder(User $user, Test $test)
    {
        $testReminder = new TestReminder($test, $user);
        $testReminder->sendEmail();
        $testReminder->sendPushNotification();
    }

    /**
     * Sends a mail with csv result to the given mail address.
     * @param $email
     * @param Test $test
     * @param Reminder $reminder
     * @param boolean $showPersonalData
     */
    public function sendTestResults($email, Test $test, Reminder $reminder, bool $showPersonalData)
    {
        $testResultReminder = new TestResultReminder($reminder, $test, $email, $showPersonalData);
        $testResultReminder->sendEmail($email);
    }

    /**
     * Sends a course reminder to an user.
     * @param User $user
     * @param Course $course
     */
    public function sendCourseReminder(User $user, Course $course)
    {
        $courseReminder = new CourseReminder($course, $user, app(CoursesEngine::class));
        $courseReminder->sendEmail();
        $courseReminder->sendPushNotification();
    }

    /**
     * Sends a new course notification to an user.
     * @param User $user
     * @param Course $course
     */
    public function sendNewCourseNotification(User $user, Course $course)
    {
        $newCourseNotification = new NewCourseNotification($course, $user);
        $newCourseNotification->sendEmail();
        $newCourseNotification->sendPushNotification();
    }

    /**
     * Sends a mail with csv result to the given mail address.
     *
     * @param string $email
     * @param Course $course
     * @param boolean $showPersonalData
     * @param boolean $showEmails
     * @param User $manager
     */
    public function sendCourseResults(string $email, Course $course, bool $showPersonalData, bool $showEmails, User $manager=null )
    {
        $courseResultReminder = new CourseResultReminder($course,$manager, $showPersonalData, $showEmails);
        $courseResultReminder->sendEmail($email);
    }

    /**
     * Sends a passed course to a user.
     * @param User $user
     * @param Course $course
     * @param CourseParticipation $participation
     */
    public function sendPassedCourse(User $user, Course $course, CourseParticipation $participation)
    {
        $passedCourse = new PassedCourse($course, $participation, $user);
        $passedCourse->sendEmail();
        $passedCourse->sendPushNotification();
    }

    /**
     * Sends a mail with a reminder to the given mail address.
     *
     * @param string $email
     * @param Course $course
     */
    public function sendRepetitionCourseReminder(string $email, Course $course)
    {
        $repetitionCourseReminder = new RepetitionCourseReminder($course);
        $repetitionCourseReminder->sendEmail($email);
    }

    /**
     * Sends a direct message from an  admin.
     * @param User $user
     * @param string $message
     * @return void
     */
    public function sendDirectMessage(User $user, string $message)
    {
        $directMessage = new DirectMessage($message, $user);
        $directMessage->sendEmail();
        $directMessage->sendPushNotification();
    }

    /**
     * Sends the notification to the reporter about the deleted comment.
     *
     * @param User $user
     * @param Comment $comment
     * @param string|null $statusExplanation
     */
    public function sendDeletedCommentNotificationForReporter(User $user, Comment $comment, ?string $statusExplanation = null)
    {
        $commentDeletedForReporter = new CommentDeletedForReporter($statusExplanation, $comment, $user);
        $commentDeletedForReporter->sendEmail();
        $commentDeletedForReporter->sendPushNotification();
    }

    /**
     * Sends the notification to the author about the deleted comment.
     *
     * @param User $user
     * @param Comment $comment
     * @param string|null $statusExplanation
     */
    public function sendDeletedCommentNotificationForAuthor(User $user, Comment $comment, ?string $statusExplanation = null)
    {
        $commentDeletedForAuthor = new CommentDeletedForAuthor($statusExplanation, $comment, $user);
        $commentDeletedForAuthor->sendEmail();
        $commentDeletedForAuthor->sendPushNotification();
    }

    /**
     * Sends the notification about the harmless comment.
     *
     * @param User $user
     * @param Comment $comment
     * @param string|null $statusExplanation
     */
    public function sendNotDeletedCommentNotification(User $user, Comment $comment, ?string $statusExplanation = null)
    {
        $commentNotDeleted = new CommentNotDeleted($statusExplanation, $comment, $user);
        $commentNotDeleted->sendEmail();
        $commentNotDeleted->sendPushNotification();
    }

    /**
     * Sends the notification when a subscribed content has a new comment.
     *
     * @param int $contentType
     * @param int $contentId
     * @param Comment $newComment
     * @throws Exception
     */
    public function sendSubscriptionCommentNotification(int $contentType, int $contentId, Comment $newComment)
    {
        dispatch(function() use ($contentType, $contentId, $newComment) {
            $subscribedUserIds = NotificationSubscription::subscribedUserIds($contentType, $contentId);

            // if there is a new comment on a course content attempt,
            // always trigger subscriptions on the parent content as well
            if (
                $contentType === MorphTypes::TYPE_COMMENT
                && $newComment->foreign_type === MorphTypes::TYPE_COURSE_CONTENT_ATTEMPT
            ) {
                $additionalSubscribedUserIds = NotificationSubscription::subscribedUserIds($newComment->foreign_type, $newComment->foreign_id);
                $subscribedUserIds = $subscribedUserIds
                    ->concat($additionalSubscribedUserIds)
                    ->unique();
            }

            foreach ($subscribedUserIds as $recipientId) {
                if ($recipientId === $newComment->author_id) {
                    continue;
                }
                $subscriptionCommentNotification = new SubscriptionComment($contentType, $contentId, $newComment, $recipientId);
                $subscriptionCommentNotification->sendEmail();
                $subscriptionCommentNotification->sendPushNotification();
            }
        })->afterResponse();
    }

    /**
     * Sends the notification about the appointment.
     *
     * @param User $user
     * @param Appointment $appointment
     * @throws Exception
     */
    public function sendNewAppointment(User $user, Appointment $appointment)
    {
        $newAppointment = new NewAppointment($appointment, $user);
        $newAppointment->sendEmail();
        $newAppointment->sendPushNotification();
    }

    /**
     * Sends the notification about updating of the appointment start date.
     *
     * @param User $user
     * @param Appointment $appointment
     * @param int $changeKind
     */
    public function sendAppointmentStartDateWasUpdated(User $user, Appointment $appointment, int $changeKind)
    {
        $appointmentStartDateWasUpdated = new AppointmentStartDateWasUpdated($appointment, $user, $changeKind);
        $appointmentStartDateWasUpdated->sendEmail();
        $appointmentStartDateWasUpdated->sendPushNotification();
    }

    /**
     * Sends the appointment reminder.
     *
     * @param User $user
     * @param Appointment $appointment
     */
    public function sendAppointmentReminder(User $user, Appointment $appointment)
    {
        $appointmentReminder = new AppointmentReminder($appointment, $user);
        $appointmentReminder->sendEmail();
        $appointmentReminder->sendPushNotification();
    }

    /**
     * Sends the email change confirmation.
     *
     * @param User $user
     * @param string $newEmail
     */
    public function sendEmailChangeConfirmation(User $user, string $newEmail)
    {
        $emailChangeConfirmation = new EmailChangeConfirmation($newEmail, $user);
        $emailChangeConfirmation->sendEmail($newEmail);
    }

    /**
     * Sends the user deletion request.
     *
     * @param User $user
     */
    public function sendUserDeletionRequest(User $user)
    {
        $userDeletionRequest = new UserDeletionRequest($user);
        $userDeletionRequest->sendEmail($user->app->getNotificationMails());
    }
}
