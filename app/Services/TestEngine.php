<?php

namespace App\Services;

use App\Mail\Mailer;
use App\Models\AnalyticsEvent;
use App\Models\AppProfile;
use App\Models\Category;
use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\Reminder;
use App\Models\Test;
use App\Models\TestQuestion;
use App\Models\TestSubmission;
use App\Models\TestSubmissionAnswer;
use App\Models\User;
use Carbon\Carbon;
use DB;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TestEngine
{

    /**
     * Create a query for tests using filter
     * @param $appId
     * @param null $search
     * @param null $tags
     * @param null $filter
     * @param null $orderBy
     * @param false $descending
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function testsFilterQuery($appId, $admin, $search = null, $tags = null, $filter = null, $orderBy = null, $descending = false) {
        $testQuery = Test::ofApp($appId);

        if ($admin) {
            $testQuery = $testQuery->tagRights($admin);
        }

        if ($search) {
            $matchingTitles = DB::table('test_translations')
                ->join('tests', 'test_translations.test_id', '=', 'tests.id')
                ->select('tests.id')
                ->where('tests.app_id', $appId)
                ->whereRaw('test_translations.name LIKE ?', '%'.escapeLikeInput($search).'%');
            $testQuery->where(function ($query) use ($search, $matchingTitles) {
                $query->whereIn('id', $matchingTitles)
                    ->orWhere('id', extractHashtagNumber($search));
            });
        }
        if ($filter === 'visible') {
            $testQuery->where('archived', false);
            $testQuery->where(function ($query) {
                $query->where('active_until', '>=', Carbon::now()->startOfDay())->orWhereNull('active_until');
            });
        }
        if ($filter === 'expired') {
            $testQuery->where('archived', false);
            $testQuery->where('active_until', '<', Carbon::now()->startOfDay());
        }
        if ($filter === 'archived') {
            $testQuery->where('archived', true);
        }
        if ($filter === 'archived_expired') {
            $testQuery->where(function ($query) {
                $query->where('archived', true);
                $query->orWhere('active_until', '<', Carbon::now()->startOfDay());
            });
        }

        if ($tags && count($tags)) {
            $newsWithoutTag = in_array(-1, $tags);
            $tags = array_filter($tags, function ($tag) {
                return $tag !== '-1';
            });
            if (count($tags)) {
                $testQuery->where(function (Builder $query) use ($tags, $newsWithoutTag) {
                    $query->whereHas('tags', function ($query) use ($tags) {
                        $query->whereIn('tags.id', $tags);
                    });
                    if ($newsWithoutTag) {
                        $query->orWhereDoesntHave('tags');
                    }
                });
            } else {
                $testQuery->doesntHave('tags');
            }
        }

        if ($orderBy) {
            $testQuery->orderBy($orderBy, $descending ? 'desc' : 'asc');
        }
        return $testQuery;
    }
    /**
     * Returns all tests which were completed by the user.
     *
     * @param User $user
     *
     * @return Collection
     */
    public function getUsersFinishedTests(User $user)
    {
        $submissions = TestSubmission::where('user_id', $user->id)->orderBy('created_at', 'DESC')->with('test')->get();

        return $submissions;
    }

    /**
     * Returns all tests which are currently available for the user.
     *
     * @param User $user
     *
     * @return Collection
     */
    public function getUsersAvailableTests(User $user)
    {
        $usersTags = $user->tags->pluck('id');
        $userQuizTeams = $user->quizTeams->pluck('id');

        // Fetch the tests which are generally available for the user
        /** @var Collection $tests */
        $tests = Test::ofApp($user->app_id)
            ->with('tags')
            ->where('archived', 0)
            ->where(function ($q) {
                $q->where('active_until', '>', date('Y-m-d H:i:s'))
                  ->orWhereNull('active_until');
            })
            ->where(function ($q) use ($usersTags, $userQuizTeams) {
                // The user can see the test when no tag and no quiz team is set
                $q->where(function ($q2) {
                    $q2->doesntHave('tags');
                    $q2->where(function ($q3) {
                        $q3->where('tests.quiz_team_id', 0)
                         ->orWhereNull('tests.quiz_team_id');
                    });
                })
                ->orWhere(function ($q2) use ($usersTags, $userQuizTeams) {
                    $q2
                        ->whereHas('tags', function ($q3) use ($usersTags) {
                            $q3->whereIn('tags.id', $usersTags);
                        })
                        ->orWhereIn('tests.quiz_team_id', $userQuizTeams);
                });
            })
            ->get();

        // We have to filter the tests to see if they are really available
        // They might not be if the user already took the test and
        // has used up all attempts
        return $tests->filter(function ($test) use ($user) {
            return $this->submissionAvailable($test, $user->id);
        });
    }

    /**
     * Returns all tests which are currently visible for the user.
     *
     * @param User $user
     *
     * @return Collection
     */
    public function getUsersVisibleTests(User $user)
    {
        $usersTags = $user->tags->pluck('id');
        $quizTeamIds = $user->quizTeams->pluck('id');

        /** @var Collection $tests */
        $tests = Test::ofApp($user->app_id)
            ->with('tags')
            ->where('archived', 0)
            ->where(function ($q) use ($usersTags, $quizTeamIds) {
                // The user can see the test when no tag and no quiz team is set
                $q->where(function ($q2) {
                    $q2->doesntHave('tags');
                    $q2->where(function ($q3) {
                        $q3->where('tests.quiz_team_id', 0)
                            ->orWhereNull('tests.quiz_team_id');
                    });
                })
                // They can also see it when they overlap a tag id or quiz team id with the test
                ->orWhere(function ($q2) use ($usersTags, $quizTeamIds) {
                    $q2
                        ->whereHas('tags', function ($q3) use ($usersTags) {
                            $q3->whereIn('tags.id', $usersTags);
                        })
                        ->orWhereIn('tests.quiz_team_id', $quizTeamIds);
                });
            })
            ->get();

        return $tests;
    }

    public function hasAccess(User $user, Test $test)
    {
        if ($test->app_id !== $user->app_id) {
            return false;
        }
        if ($test->archived) {
            return false;
        }
        $testTags = $test->tags->pluck('id');
        $testQuizTeam = $test->quiz_team_id;
        if (! $testTags->count() && ! $testQuizTeam) {
            return true;
        }

        $usersTags = $user->tags->pluck('id');
        if ($usersTags->intersect($testTags)->count() > 0) {
            return true;
        }

        $quizTeamIds = $user->quizTeams->pluck('id');
        if ($quizTeamIds->contains($testQuizTeam)) {
            return true;
        }

        return false;
    }

    public function submissionAvailable(Test $test, $user_id)
    {
        if ($test->hasEndDate() && $test->active_until->lt(Carbon::now())) {
            return false;
        }

        if (! $test->repeatable_after_pass) {
            $hasPassed = (bool) TestSubmission::where('test_id', $test->id)
                ->where('user_id', $user_id)
                ->where('result', 1)
                ->count();
            if ($hasPassed) {
                return false;
            }
        }

        if (! $test->attempts) {
            return true;
        }

        $attempts = TestSubmission::where('test_id', $test->id)
            ->where('user_id', $user_id)
            ->whereNotNull('result')
            ->count();

        return $attempts < $test->attempts;
    }

    /**
     * Returns the last submission which has been submitted.
     *
     * @param Test $test
     * @param $user_id
     * @return mixed
     */
    public function getLastFinishedSubmission(Test $test, $user_id)
    {
        return TestSubmission::where('test_id', $test->id)
            ->where('user_id', $user_id)
            ->whereNotNull('result')
            ->orderBy('created_at', 'DESC')
            ->first();
    }

    public function getLastFinishedTestSubmissions($testIds, $userIds) {
        /**
         * SELECT * FROM test_submissions
         * INNER JOIN (
         *  SELECT user_id, test_id, max(updated_at) as last_updated_at
         *  FROM test_submissions
         *  WHERE test_id IN $testIds
         *  AND user_id IN $userIds
         *  AND result IS NOT NULL
         *  GROUP BY user_id, test_id
         * ) as latest_submissions
         * ON test_submissions.user_id = latest_submissions.user_id
         * AND test_submissions.test_id = latest_submissions.test_id
         * AND test_submissions.updated_at = latest_submissions.last_updated_at;
         */
        $latestSubmissions = DB::table('test_submissions')
            ->select(['user_id', 'test_id'])
            ->selectRaw('max(updated_at) as last_updated_at')
            ->whereIn('test_id', $testIds)
            ->whereIn('user_id', $userIds)
            ->whereNotNull('result')
            ->groupBy(['user_id', 'test_id']);
        return TestSubmission::selectRaw('*')
            ->joinSub($latestSubmissions, 'latest_submissions', function ($join) {
                $join->on('test_submissions.user_id', '=', 'latest_submissions.user_id')
                    ->on('test_submissions.test_id', '=', 'latest_submissions.test_id')
                    ->on('test_submissions.updated_at', '=', 'latest_submissions.last_updated_at');
            })->get();
    }

    /**
     * Returns the last submission which has been submitted.
     *
     * @param Test $test
     * @param $user_id
     * @return mixed
     */
    public function getLastSubmission(Test $test, $user_id)
    {
        return TestSubmission::where('test_id', $test->id)
            ->where('user_id', $user_id)
            ->orderBy('created_at', 'DESC')
            ->first();
    }

    public function getCurrentSubmission($test_id, $user_id)
    {
        return TestSubmission::where('test_id', $test_id)
                                    ->where('user_id', $user_id)
                                    ->whereNull('result')
                                    ->first();
    }

    public function getCurrentQuestion(Test $test, $user_id)
    {
        if (! $this->submissionAvailable($test, $user_id)) {
            return false;
        }

        $submission = $this->getCurrentSubmission($test->id, $user_id);
        if (! $submission) {
            $submission = new TestSubmission();
            $submission->user_id = $user_id;
            $submission->test_id = $test->id;
            $submission->result = null;
            $submission->save();

            $this->populateTestAnswers($submission);
        }

        $lastUnansweredTestAnswer = $submission->testSubmissionAnswers()
            ->orderBy('id')
            ->whereNull('result')
            ->first();
        if (! $lastUnansweredTestAnswer) {
            return null;
        }
        $currentTestQuestion = TestQuestion::with('question')->where('id', $lastUnansweredTestAnswer->test_question_id)->first();
        if (! $currentTestQuestion) {
            // This case is for dynamic tests
            $currentTestQuestion = new TestQuestion;
            $currentTestQuestion->question = $lastUnansweredTestAnswer->question;
            $currentTestQuestion->position = $submission->testSubmissionAnswers()->whereNotNull('result')->count();
        }

        return $currentTestQuestion;
    }

    public function getQuestionPosition(Test $test, $user_id, TestQuestion $testQuestion)
    {
        if (! $this->submissionAvailable($test, $user_id)) {
            return false;
        }

        $submission = $this->getCurrentSubmission($test->id, $user_id);

        if($testQuestion->id) {
            // Static tests
            $testAnswers = $submission->testSubmissionAnswers()
                ->orderBy('id')
                ->pluck('test_question_id');
            $index = $testAnswers->search($testQuestion->id);
        } else {
            // Dynamic tests
            $testAnswers = $submission->testSubmissionAnswers()
                ->orderBy('id')
                ->pluck('question_id');
            $index = $testAnswers->search($testQuestion->question->id);
        }
        if($index === false) {
            $index = 0;
            report(new Exception('Got index 0 which shouldnt happen. user: ' . $user_id . ' testQuestion: ' . $testQuestion->id . ' submission: ' . $submission->id));
        }
        return $index;
    }

    public function populateTestAnswers(TestSubmission $submission)
    {
        if ($submission->test->mode == Test::MODE_QUESTIONS) {
            foreach ($submission->test->testQuestions()->orderBy('position')->get() as $testQuestion) {
                $testAnswer = new TestSubmissionAnswer();
                $testAnswer->test_submission_id = $submission->id;
                $testAnswer->test_question_id = $testQuestion->id;
                $testAnswer->question_id = $testQuestion->question_id;
                $testAnswer->result = null;
                $testAnswer->answer_ids = null;
                $testAnswer->save();
            }
        }

        if ($submission->test->mode == Test::MODE_CATEGORIES) {
            $questionIds = [];
            foreach ($submission->test->testCategories as $testCategory) {
                $categoryQuestionIds = Question::where('app_id', $submission->test->app_id)
                    ->where('visible', 1)
                    ->where('category_id', $testCategory->category_id)
                    ->where('type', '!=', Question::TYPE_INDEX_CARD)
                    ->limit($testCategory->question_amount)
                    ->inRandomOrder()
                    ->pluck('id')
                    ->toArray();
                $questionIds = array_merge($questionIds, $categoryQuestionIds);
            }
            shuffle($questionIds);

            foreach ($questionIds as $questionId) {
                $testAnswer = new TestSubmissionAnswer();
                $testAnswer->test_submission_id = $submission->id;
                $testAnswer->question_id = $questionId;
                $testAnswer->result = null;
                $testAnswer->answer_ids = null;
                $testAnswer->save();
            }
        }
    }

    public function saveAnswer($test_id, $user_id, $test_question_id, $answers)
    {
        $submission = $this->getCurrentSubmission($test_id, $user_id);
        if ($submission->result !== null) {
            return false;
        }

        // Identify by test_question_id
        if ($submission->test->mode == Test::MODE_QUESTIONS) {
            $answer = TestSubmissionAnswer::where('test_question_id', $test_question_id)
                                          ->where('test_submission_id', $submission->id)
                                          ->first();
        }

        // Identify by question_id
        if ($submission->test->mode == Test::MODE_CATEGORIES) {
            $answer = TestSubmissionAnswer::where('question_id', $test_question_id)
                                          ->where('test_submission_id', $submission->id)
                                          ->first();
        }

        if (is_array($answers)) {
            $answer->answer_ids = implode(',', $answers);
        } else {
            $answer->answer_ids = $answers;
        }
        $this->setAnswerResult($answer);

        return true;
    }

    private function setAnswerResult(TestSubmissionAnswer $answer)
    {
        $answers = explode(',', $answer->answer_ids);
        $correct = true;
        foreach ($answer->question->questionAnswers as $questionAnswer) {
            if ($questionAnswer->correct && ! in_array($questionAnswer->id, $answers)) {
                $correct = false;
                break;
            }
            if (! $questionAnswer->correct && in_array($questionAnswer->id, $answers)) {
                $correct = false;
                break;
            }
        }
        $answer->result = $correct ? 1 : 0;
        $answer->save();
    }

    /**
     * Returns the submission results.
     *
     * @param TestSubmission $submission
     * @return Builder[]|Collection|HasMany[]|\Illuminate\Support\Collection
     * @throws Exception
     */
    public function getSubmissionResults(TestSubmission $submission)
    {
        /** @var TranslationEngine $translationEngine */
        $translationEngine = app(TranslationEngine::class);
        /** @var QuestionsEngine $questionsEngine */
        $questionsEngine = app(QuestionsEngine::class);
        $appId = $submission->test->app_id;
        $appSettings = new AppSettings($submission->test->app_id);
        $app = $appSettings->getApp();
        /** @var AppProfile $appProfile */
        $appProfile = $submission->user->getAppProfile();
        $results = $submission
            ->testSubmissionAnswers()
            ->with('testQuestion', 'testQuestion.question')
            ->with('question')
            ->get();

        $questionAnswers = null;

        if (! $appProfile->getValue('hide_given_test_answers')) {
            $questionAnswerIds = [];
            foreach ($results as $result) {
                $questionAnswerIds = array_merge($questionAnswerIds, explode(',', $result->answer_ids));
            }

            $questionAnswers = QuestionAnswer::withTranslation()->whereHas('question', function ($query) use ($submission) {
                $query->where('app_id', $submission->test->app_id);
            })->whereIn('id', $questionAnswerIds)->get();
            $questions = $results->map(function ($result) {
                return $result->question;
            });
            $questions = $translationEngine->attachQuestionTranslations($questions, $app);
            $translationEngine->attachQuestionAnswers($questions, $app);
            $translationEngine->attachQuestionAttachments($questions);
            $questions = array_map(function ($question) use ($appId, $questionsEngine) {
                return $questionsEngine->formatQuestionForFrontend($question, $appId);
            }, $questions->toArray());
        }

        $results = $results->map(function ($answer) use ($questionAnswers) {
            if ($questionAnswers) {
                $answer->given_answers = $questionAnswers->whereIn('id', explode(',', $answer->answer_ids))->pluck('content');
            }

            return [
                'min_rate' => $answer->min_rate,
                'result' => $answer->result,
                'given_answers' => $answer->given_answers,
                'test_question' => [
                    'id' => $answer->test_question_id ?: $answer->question_id,
                    'question' => $answer->question,
                    'realpoints' => $answer->testQuestion ? $answer->testQuestion->realpoints : $answer->question->category->points,
                ],
            ];
        });

        if ($submission->result === null) {
            $this->determineSubmissionResult($submission);
        }

        return $results;
    }

    /**
     * Returns the submission results with answers.
     *
     * @param TestSubmission $submission
     * @return Builder[]|Collection|HasMany[]|\Illuminate\Support\Collection
     * @throws Exception
     */
    public function getSubmissionResultsWithAnswers(TestSubmission $submission)
    {
        /** @var TranslationEngine $translationEngine */
        $translationEngine = app(TranslationEngine::class);
        /** @var QuestionsEngine $questionsEngine */
        $questionsEngine = app(QuestionsEngine::class);
        $appId = $submission->test->app_id;
        $appSettings = new AppSettings($submission->test->app_id);
        $app = $appSettings->getApp();
        $results = $submission
            ->testSubmissionAnswers()
            ->with('testQuestion')
            ->get();

        $questions = DB::table('questions')
            ->select(['id', 'category_id', 'type'])
            ->whereIn('id', $results->pluck('question_id'))
            ->get()
            ->keyBy('id');
        $categories = Category
            ::withTranslation()
            ->whereIn('id', $questions->pluck('category_id'))
            ->get()
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'points' => $category->points,
                ];
            })
            ->keyBy('id');
        $translationEngine->attachQuestionTranslations($questions, $app);
        $translationEngine->attachQuestionAnswers($questions, $app);
        $translationEngine->attachQuestionAttachments($questions);
        $questions->transform(function ($question) use ($appId, $questionsEngine, $categories) {
            $question->category = $categories->get($question->category_id);

            return $questionsEngine->formatQuestionForFrontend($question, $appId);
        });
        $questions = $questions->keyBy('id');

        $results = $results->map(function ($testSubmissionAnswer) use ($questions) {
            $question = $questions->get($testSubmissionAnswer->question_id);
            $givenAnswers = collect($question->answers)->whereIn('id', explode(',', $testSubmissionAnswer->answer_ids))->pluck('id');

            $points = 0;
            if ($testSubmissionAnswer->testQuestion) {
                $points = $testSubmissionAnswer->testQuestion->points;
            }
            if (! $points && $question->category) {
                $points = $question->category['points'];
            }

            return [
                'id' => $testSubmissionAnswer->id,
                'result' => $testSubmissionAnswer->result,
                'given_answers' => $givenAnswers,
                'test_question' => [
                    'question' => $question,
                    'realpoints' => $points,
                ],
            ];
        });

        if ($submission->result === null) {
            $this->determineSubmissionResult($submission);
        }

        return $results;
    }

    /**
     * Saves the result to the submission and sends the test passed email if applicable.
     *
     * @param TestSubmission $submission
     */
    private function determineSubmissionResult(TestSubmission $submission)
    {
        if ($submission->test->hasEndDate() && $submission->test->active_until->lt(Carbon::now())) {
            $submission->result = false;
        } else {
            $min_rate = $submission->test->min_rate;
            if ($submission->percentage() >= $min_rate) {
                $submission->result = true;
            } else {
                $submission->result = false;
            }
            $submission->save();
        }
        if (! $submission->result) {
            return;
        }
        /** @var Mailer $mailer */
        $mailer = app(Mailer::class);
        $mailer->sendTestPassed($submission);

        $awardTagWithGroupIds = $submission->test->awardTags()
            ->whereNotNull('tag_group_id')
            ->pluck('tag_group_id');

        $userTagsWithoutNewGroups = $submission->user->tags()
            ->where(function ($query) use ($awardTagWithGroupIds) {
                $query->whereNotIn('tag_group_id', $awardTagWithGroupIds);
                $query->orWhereNull('tag_group_id');
            })
            ->pluck('tag_id');

        // add new tags, remove dupes
        $userTagIds = $userTagsWithoutNewGroups
            ->merge($submission->test->awardTags()->pluck('tag_id'))
            ->unique()
            ->values();

        $submission->user->tags()->sync($userTagIds);

        AnalyticsEvent::log($submission->user, AnalyticsEvent::TYPE_TEST_SUCCESS, $submission->test);
    }

    /**
     * @param \Illuminate\Support\Collection $tests
     * @return \Illuminate\Support\Collection
     */
    public static function attachHasReminders(\Illuminate\Support\Collection $tests)
    {
        if (! $tests->count()) {
            return $tests;
        }
        $reminders = Reminder
            ::select(['foreign_id', DB::raw('COUNT(*) as c')])
            ->whereIn('foreign_id', $tests->pluck('id'))
            ->whereIn('type', [Reminder::TYPE_USER_TEST_NOTIFICATION,  Reminder::TYPE_TEST_RESULTS])
            ->groupBy('foreign_id')
            ->pluck('c', 'foreign_id');
        $tests->transform(function ($test) use ($reminders) {
            $test->remindersExist = (bool) $reminders->get($test->id, 0);
            return $test;
        });
        return $tests;
    }
}
