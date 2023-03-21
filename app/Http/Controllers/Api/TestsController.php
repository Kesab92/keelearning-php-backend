<?php

namespace App\Http\Controllers\Api;

use App\Http\APIError;
use App\Http\Controllers\Controller;
use App\Models\AzureVideo;
use App\Models\CertificateTemplate;
use App\Models\QuestionAttachment;
use App\Models\Test;
use App\Models\TestSubmission;
use App\Services\AppSettings;
use App\Services\TestEngine;
use App\Services\TestSubmissionRenderer;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Redis as Redis;
use Response;
use URL;

class TestsController extends Controller
{
    /**
     * @var TestEngine
     */
    private $testEngine;

    public function __construct(TestEngine $testEngine)
    {
        parent::__construct();
        $this->testEngine = $testEngine;
    }

    public function tests()
    {
        $user = user();
        $tests = $this->testEngine->getUsersAvailableTests($user);

        $sortAlphabetically = false;
        if ($appSettings = app(AppSettings::class, [$user->app_id])) {
            $sortAlphabetically = $appSettings->getValue('sort_tests_alphabetically');
        }
        if ($sortAlphabetically) {
            $tests = $tests->sort(function ($a, $b) {
                return strcmp(utrim(strtolower($a->name)), utrim(strtolower($b->name)));
            });
        }

        return Response::json([
            'tests' => array_values($tests->toArray()),
        ]);
    }

    public function testsWithResults()
    {
        $user = user();
        $tests = $this->testEngine->getUsersVisibleTests($user);
        /** @var Collection $submissions */
        $submissions = TestSubmission
            ::where('user_id', $user->id)
            ->get()
            ->groupBy('test_id');
        $certificateTemplates = CertificateTemplate
            ::whereIn('test_id', $tests->pluck('id'))
            ->leftJoin(
                'certificate_template_translations',
                'certificate_template_translations.certificate_template_id',
                '=',
                'certificate_templates.id')
            ->where('certificate_template_translations.language', language())
            ->where('certificate_template_translations.background_image', '>', '')
            ->where('certificate_template_translations.elements', '>', '')
            ->where('certificate_template_translations.background_image_size', '>', '')
            ->pluck('test_id')
            ->toArray();
        $tests->transform(function (Test $test) use ($submissions, $certificateTemplates) {
            $testSubmissions = collect($submissions->get($test->id, []));
            $testSubmissions->transform(function ($submission) {
                $data = $submission->only([
                    'id',
                    'result',
                ]);
                $data['created_at'] = $submission->created_at->format('Y-m-d H:i:s');

                return $data;
            });
            $data = $test->only([
                'id',
                'active_until',
                'attempts',
                'min_rate',
                'minutes',
                'mode',
                'name',
                'description',
                'no_download',
                'repeatable_after_pass',
                'icon_url',
            ]);
            $data['has_certificate'] = in_array($test->id, $certificateTemplates);
            $data['created_at'] = $test->created_at->format('Y-m-d H:i:s');
            $data['cover_image_url'] = $test->cover_image_url;
            $data['active_until'] = $test->active_until ? $test->active_until->format('Y-m-d H:i:s') : null;
            $data['submissions'] = array_values($testSubmissions->toArray());

            return $data;
        });

        return Response::json([
            'tests' => array_values($tests->toArray()),
        ]);
    }

    public function testResults()
    {
        $visibleTestIds = $this->testEngine->getUsersVisibleTests(user())->pluck('id');
        $testSubmissions = $this
            ->testEngine
            ->getUsersFinishedTests(user())
            ->filter(function(TestSubmission $submission) use ($visibleTestIds) {
                return $visibleTestIds->contains($submission->test_id);
            })
            ->values();
        return Response::json([
            'testResults' => $testSubmissions,
        ]);
    }

    /**
     * Returns the requested test and information if it can be started.
     *
     * @param $test_id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function test($test_id)
    {
        $test = Test::find($test_id);
        $user = user();

        if (! $this->testEngine->hasAccess($user, $test)) {
            app()->abort(403, __('errors.test_no_access'));
        }

        $test->repeatable = $test->isRepeatable($user); // LEGACY frontends

        $lastFinishedSubmission = $this->testEngine->getLastFinishedSubmission($test, $user->id);

        if ($lastFinishedSubmission) {
            $lastFinishedSubmission = $lastFinishedSubmission->only('id');
        }

        $submissions = TestSubmission::where('test_id', $test->id)
            ->where('user_id', $user->id)
            ->whereNotNull('result')
            ->get();
        $submissionCount = $submissions->count();
        $testPassed = (bool) $submissions->where('result', 1)->count();

        return Response::json([
            'lastFinishedSubmission' => $lastFinishedSubmission,
            'questionCount'          => $test->question_count,
            'submissionAvailable'    => $this->testEngine->submissionAvailable($test, $user->id),
            'submissionCount'        => $submissionCount,
            'test'                   => $test->toArray(),
            'testPassed'             => $testPassed,
        ]);
    }

    /**
     * Returns the requested test and information if it can be started.
     *
     * @param $test_id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function testDetails($test_id)
    {
        /** @var Test $test */
        $test = Test::find($test_id);
        $user = user();

        if (! $this->testEngine->hasAccess($user, $test)) {
            app()->abort(403, __('errors.test_no_access'));
        }

        $submissions = TestSubmission
            ::with('testSubmissionAnswers')
            ->where('test_id', $test->id)
            ->where('user_id', $user->id)
            ->get()
            ->transform(function (TestSubmission $submission) {
                return [
                    'id' => $submission->id,
                    'created_at' => $submission->created_at->format('Y-m-d H:i:s'),
                    'percentage' => $submission->percentage(),
                    'result' => $submission->result,
                ];
            })
            ->sortByDesc('percentage');

        $certificateLink = $this->getCertificateLink($test, $submissions);

        return Response::json([
            'questionCount' => $test->question_count,
            'submissions'   => $submissions->values(),
            'certificateLink' => $certificateLink,
            'points'        => $test->points(),
        ]);
    }

    private function getCertificateLink(Test $test, $submissions) {
        if($test->no_download) {
            return null;
        }
        $submissions = $submissions->where('result', 1);
        if(!$submissions->count()) {
            return null;
        }
        if(!$test->hasCertificateTemplate()) {
            return null;
        }
        $bestSubmission = $submissions->first();
        if (!$bestSubmission) {
            return null;
        }

        return URL::signedRoute('certificateDownload', [
            'lang' => language(),
            'submission_id' => $bestSubmission['id'],
        ]);
    }

    public function currentQuestion($test_id)
    {
        $test = Test::find($test_id);
        $user = user();

        if (! $test) {
            app()->abort(404, __('errors.test_not_found'));
        }

        if (! $this->testEngine->hasAccess($user, $test)) {
            app()->abort(403, __('errors.test_no_access'));
        }

        $currentTestQuestion = $this->testEngine->getCurrentQuestion($test, $user->id);

        if (! $currentTestQuestion) {
            $submission = $this->testEngine->getLastSubmission($test, $user->id);

            return Response::json([
                'status' => 'done',
                'submission_id' => $submission->id,
            ]);
        }
        $currentTestQuestionPosition = $this->testEngine->getQuestionPosition($test, $user->id, $currentTestQuestion);
        $question = $currentTestQuestion->question;
        $answers = [];
        foreach ($question->questionAnswers as $answer) {
            $answers[] = [
                'id' => $answer->id,
                'content' => $answer->content,
            ];
        }
        shuffle($answers);
        $categoryGroup = $question->category->categorygroup;
        $attachments = $question->attachments->map(function ($attachment) use ($test) {
            if ($attachment->type === QuestionAttachment::ATTACHMENT_TYPE_AUDIO || $attachment->type === QuestionAttachment::ATTACHMENT_TYPE_IMAGE) {
                $attachment->attachment = formatAssetURL($attachment->attachment);
                $attachment->attachment_url = formatAssetURL($attachment->attachment_url);
            }
            if ($attachment->type === QuestionAttachment::ATTACHMENT_TYPE_AZURE_VIDEO) {
                $azureVideo = AzureVideo::where('app_id', $test->app_id)->where('id', $attachment->attachment)->first();
                if ($azureVideo) {
                    $attachment->attachment = $azureVideo->streaming_url;
                } else {
                    $attachment->attachment = '';
                }
            }

            return $attachment;
        });
        $questionData = [
            'id'              => $question->id,
            'answers'         => $answers,
            'attachments'     => $attachments,
            'category_color'  => $question->category->color,
            'category_parent' => $categoryGroup ? $categoryGroup->name : null,
            'category'        => $question->category->name,
            'title'           => $question->title,
            'type'            => $question->type,
        ];

        return Response::json([
            'status'   => 'question',
            'id'       => $currentTestQuestion->id ?: $question->id,
            'position' => $currentTestQuestionPosition,
            'question' => $questionData,
        ]);
    }

    public function saveAnswer(Request $request, $test_id)
    {
        $test = Test::find($test_id);
        $user = user();

        if (! $this->testEngine->hasAccess($user, $test)) {
            app()->abort(403, __('errors.test_no_access'));
        }

        $save = $this->testEngine->saveAnswer($test_id, $user->id, $request->get('test_question_id'), $request->get('answers'));
        if (! $save) {
            return new APIError(__('errors.question_already_answered'));
        }

        return Response::json([
            'success' => true,
        ]);
    }

    public function results($submission_id)
    {
        $submission = $this->getSubmission($submission_id);
        $results = $this->testEngine->getSubmissionResults($submission);

        $hasCertificateTemplate = $submission->test->hasCertificateTemplate();
        $hasCertificate = $hasCertificateTemplate && $submission->result && ! $submission->test->no_download;

        $certificateLink = null;
        if ($hasCertificate) {
            $certificateLink = URL::signedRoute('certificateDownload', [
                'submission_id' => $submission->id,
            ]);
        }

        return Response::json([
            'results' => $results,
            'certificateLink' => $certificateLink,
        ]);
    }

    public function resultsWithAnswers($submission_id)
    {
        $submission = $this->getSubmission($submission_id);
        $results = $this->testEngine->getSubmissionResultsWithAnswers($submission);

        $hasCertificateTemplate = $submission->test->hasCertificateTemplate();
        $hasCertificate = $hasCertificateTemplate && $submission->result && ! $submission->test->no_download;

        $certificateLink = null;
        if ($hasCertificate) {
            $certificateLink = URL::signedRoute('certificateDownload', [
                'submission_id' => $submission->id,
            ]);
        }

        return Response::json([
            'results' => $results,
            'result' => $submission->result,
            'certificateLink' => $certificateLink,
        ]);
    }

    /**
     * Returns the certificate as pdf.
     *
     * @param $submission_id
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function certificate($submission_id)
    {
        /** @var TestSubmission $submission */
        $submission = TestSubmission::find($submission_id);
        if (! $submission) {
            app()->abort(403, __('errors.test_not_found'));
        }

        $hasCertificateTemplate = $submission->test->hasCertificateTemplate();
        if (! $hasCertificateTemplate || $submission->result !== 1) {
            app()->abort(404);
        }

        $lang = $submission->user->getLanguage();

        $testSubmissionRenderer = new TestSubmissionRenderer($submission, $lang);
        return $testSubmissionRenderer->render();
    }

    /**
     * Checks if the current user has access to the submission and returns it.
     *
     * @param $submission_id
     * @return TestSubmission
     */
    private function getSubmission($submission_id)
    {
        /** @var TestSubmission $submission */
        $submission = TestSubmission::find($submission_id);
        if (! $submission) {
            app()->abort(403, __('errors.test_not_found'));
        }
        if (! user()) {
            app()->abort(403, __('errors.not_logged_in'));
        }
        if ($submission->user_id != user()->id) {
            app()->abort(403, __('errors.test_no_access'));
        }

        return $submission;
    }
}
