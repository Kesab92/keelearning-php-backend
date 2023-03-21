<?php

namespace App\Http\Controllers\Api;

use App\Flatbuffers\Learningdata;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\CategoryHider;
use App\Models\LearnBoxCard;
use App\Models\LearnBoxCardUserDailyCount;
use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Services\AppSettings;
use App\Services\QuestionDifficultyEngine;
use App\Services\QuestionsEngine;
use App\Services\TranslationEngine;
use Cache;
use Carbon\Carbon;
use DB;
use Google\FlatBuffers\FlatbufferBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Request as Input;
use Response;

class LearningController extends Controller
{
    /**
     * @var QuestionsEngine
     */
    private $questionsEngine;

    public function __construct(QuestionsEngine $questionsEngine)
    {
        parent::__construct();
        $this->questionsEngine = $questionsEngine;
    }

    /**
     * @param AppSettings $settings
     * @param TranslationEngine $translationEngine
     * @param QuestionDifficultyEngine $questionDifficultyEngine
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     * @throws \Exception
     */
    public function allData(AppSettings $settings, TranslationEngine $translationEngine, QuestionDifficultyEngine $questionDifficultyEngine, QuestionsEngine $questionsEngine)
    {
        $user = user();
        $categoryIds = [];
        $categoryGroups = [];
        if ($settings->getValue('use_subcategory_system')) {
            $categoryGroups = $user->getQuestionCategoriesGrouped(CategoryHider::SCOPE_TRAINING, true);
            $categories = [];
            foreach ($categoryGroups as $categoryGroup) {
                foreach ($categoryGroup['categories'] as $category) {
                    $categories[] = $category;
                    $categoryIds[] = $category['id'];
                }
            }
        } else {
            $categories = $user->getQuestionCategories(CategoryHider::SCOPE_TRAINING, true)->toArray();
            foreach ($categories as $category) {
                $categoryIds[] = $category['id'];
            }
        }

        // We are not using eloquent here because we need to be really performant.
        $questions = DB::table('questions')
            ->where('visible', 1)
            ->whereIn('category_id', $categoryIds)
            ->select(['id', 'category_id', 'type'])
            ->get()
            ->keyBy('id');
        $translationEngine->attachQuestionAttachments($questions);
        $translationEngine->attachQuestionAnswers($questions);
        $questions = $translationEngine->attachQuestionTranslations($questions, $user->app);
        $questionDifficultyEngine->attachQuestionDifficulties($questions, $user);
        $saveData = $this->questionsEngine->getSavedata($user, $categoryIds);
        foreach ($saveData as $questionId => $saveDatum) {
            // The save data contains all categories, so it might contain entries for which we don't have a question
            if (isset($questions[$questionId])) {
                $questions[$questionId]->box = $saveDatum->box;
                $questions[$questionId]->box_entered_at = $saveDatum->box_entered_at;
            }
        }

        // Format asset URLs
        $categories = array_map(function ($category) {
            $category['cover_image'] = formatAssetURL($category['cover_image']);
            $category['cover_image_url'] = formatAssetURL($category['cover_image_url']);
            $category['category_icon'] = formatAssetURL($category['category_icon']);
            $category['category_icon_url'] = formatAssetURL($category['category_icon_url']);

            return $category;
        }, $categories);
        $questions = array_map(function ($question) use ($user, $questionsEngine) {
            return $questionsEngine->formatQuestionForFrontend($question, $user->app_id);
        }, $questions->toArray());

        $data = [
            'category_groups' => $categoryGroups,
            'categories' => $categories,
            'questions' => $questions,
            'settings' => [
                [
                    'key' => 'sort_categories_alphabetically',
                    'value' => $settings->getValue('sort_categories_alphabetically') ? 'true' : 'false',
                ],
            ],
        ];

        if (request()->get('flatbuffers') === '1') {
            return Response::make($this->getFlatbuffersLearningData($data));
        } else {
            return Response::json($data);
        }
    }

    /**
     * Convert the learning data to a flatbuffer string.
     *
     * @param $data
     * @return string
     */
    private function getFlatbuffersLearningData($data)
    {
        $builder = new FlatbufferBuilder(0);
        foreach (array_keys($data) as $k) {
            if (! is_array($data[$k])) {
                $data[$k] = array_values($data[$k]->toArray());
            } else {
                $data[$k] = array_values($data[$k]);
            }
            $data[$k] = collect($data[$k]);
        }

        $categories = $data['categories']->transform(function ($category) use ($builder) {
            $category['color'] = $builder->createString(null); // TODO: unused
            $category['created_at'] = $builder->createString($category['created_at']);
            $category['category_icon_url'] = $builder->createString($category['category_icon_url']);
            $category['cover_image_url'] = $builder->createString($category['cover_image_url']);
            $category['name'] = $builder->createString($category['name']);
            $category['updated_at'] = $builder->createString($category['updated_at']);

            return $category;
        });

        $categoryGroups = $data['category_groups']->transform(function ($categoryGroup) use ($builder) {
            $categoryGroup['created_at'] = $builder->createString($categoryGroup['created_at']);
            $categoryGroup['name'] = $builder->createString($categoryGroup['name']);
            $categoryGroup['updated_at'] = $builder->createString($categoryGroup['updated_at']);

            return $categoryGroup;
        });

        $questions = $data['questions']->transform(function ($question) use ($builder) {
            $question = (array) $question;
            if (isset($question['box_entered_at'])) {
                $question['box_entered_at'] = $builder->createString($question['box_entered_at']);
            } else {
                $question['box_entered_at'] = $builder->createString(date('Y-m-d H:i:s'));
            }
            $question['latex'] = $builder->createString($question['latex']);
            $question['title'] = $builder->createString($question['title']);

            $question['answers'] = collect($question['answers'])->transform(function ($answer) use ($builder) {
                $answer = (array) $answer;
                $answer['content'] = $builder->createString($answer['content']);
                $answer['feedback'] = $builder->createString($answer['feedback']);
                $answer['language'] = $builder->createString($answer['language']);

                return $answer;
            });
            if (isset($question['attachments'])) {
                $question['attachments'] = collect($question['attachments'])->transform(function ($attachment) use ($builder) {
                    $attachment = (array) $attachment;
                    $attachment['url'] = $builder->createString($attachment['attachment']);
                    $attachment['attachment_url'] = $builder->createString($attachment['attachment']);

                    return $attachment;
                });
            } else {
                $question['attachments'] = collect([]);
            }

            return $question;
        });

        $categories = $categories->transform(function ($category) use ($builder) {
            $categoryFlatBuffer = \App\Flatbuffers\Category::createCategory(
                $builder,
                $category['active'],
                $category['categorygroup_id'],
                null, // TODO: color is unused
                $category['created_at'],
                $category['category_icon_url'],
                $category['id'],
                $category['cover_image_url'],
                $category['name'],
                $category['points'],
                $category['updated_at']
            );

            return $categoryFlatBuffer;
        })->toArray();

        $categoryGroups = $categoryGroups->transform(function ($categoryGroup) use ($builder) {
            $categoryGroupFlatBuffer = \App\Flatbuffers\CategoryGroup::createCategoryGroup($builder, $categoryGroup['created_at'], $categoryGroup['id'], $categoryGroup['name'], $categoryGroup['updated_at']);

            return $categoryGroupFlatBuffer;
        })->toArray();

        $questions = $questions->transform(function ($question) use ($builder) {
            $answers = array_values($question['answers']->transform(function ($answer) use ($builder) {
                $answerFlatBuffer = \App\Flatbuffers\Answer::createAnswer($builder, $answer['content'], $answer['correct'], $answer['feedback'], $answer['id'], $answer['language'], $answer['question_id']);

                return $answerFlatBuffer;
            })->toArray());
            $attachments = array_values($question['attachments']->transform(function ($attachment) use ($builder) {
                $attachmentFlatBuffer = \App\Flatbuffers\Attachment::createAttachment($builder, $attachment['id'], $attachment['question_id'], $attachment['type'], $attachment['url'], $attachment['attachment_url']);

                return $attachmentFlatBuffer;
            })->toArray());
            $answersVector = \App\Flatbuffers\Question::createAnswersVector($builder, $answers);
            $attachmentsVector = \App\Flatbuffers\Question::createAttachmentsVector($builder, $attachments);

            $questionFlatBuffer = \App\Flatbuffers\Question::createQuestion($builder, isset($question['box']) ? $question['box'] : 0, $question['box_entered_at'], $question['category_id'], round($question['difficulty'], 3), $question['id'], $question['latex'], $question['title'], $question['type'], $answersVector, $attachmentsVector);

            return $questionFlatBuffer;
        })->toArray();

        $settings = $data['settings']->transform(function ($setting) use ($builder) {
            return \App\Flatbuffers\Setting::createSetting($builder, $builder->createString($setting['key']), $builder->createString($setting['value']));
        })->toArray();

        $categoriesVector = Learningdata::createCategoriesVector($builder, $categories);
        $categoryGroupsVector = Learningdata::createCategoryGroupsVector($builder, $categoryGroups);
        $questionsVector = Learningdata::createQuestionsVector($builder, $questions);
        $settingsVector = Learningdata::createSettingsVector($builder, $settings);

        $learningData = Learningdata::createLearningdata($builder, $categoriesVector, $categoryGroupsVector, $questionsVector, $settingsVector);

        $builder->finish($learningData);

        return $builder->sizedByteArray();
    }

    /**
     * Gets all categories the user can play.
     *
     *
     * @param AppSettings $settings
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function categories(AppSettings $settings, Request $request)
    {
        if ($settings->getValue('use_subcategory_system') && $request->get('groups')) {
            $categories = user()->getQuestionCategoriesGrouped(CategoryHider::SCOPE_TRAINING);
            if (! is_array($categories)) {
                $categories = $categories->toArray();
            }

            return Response::json([
                'usegroups' => true,
                'categories' => array_values($categories),
            ]);
        } else {
            $categories = user()->getQuestionCategories(CategoryHider::SCOPE_TRAINING);

            return Response::json(array_values($categories->toArray()));
        }
    }

    public function getQuestion($category_id)
    {
        /** @var Category $category */
        $category = Category::findOrFail($category_id);
        if ($category->app_id != user()->app_id) {
            app()->abort(403);
        }

        $questions = $this->questionsEngine->getCurrentQuestions(user(), $category_id);

        $question = null;
        if (count($questions)) {
            $question = $questions->random()->toArray();
            shuffle($question['question_answers']);
            $question['category'] = $category->name;
        }

        return Response::json([
            'question' => $question,
            'currentQuestionsCount' => count($questions),
        ]);
    }

    public function getFreeQuestion($category_id, TranslationEngine $translationengine)
    {
        /** @var Category $category */
        $category = Category::findOrFail($category_id);
        if ($category->app_id != user()->app_id) {
            app()->abort(403);
        }

        $questions = Question::with('questionAnswers', 'attachments', 'translationRelation')
            ->where('type', '!=', Question::TYPE_INDEX_CARD)
            ->where('visible', 1)
            ->where('category_id', $category_id)
            ->get();
        $translationengine->cacheTranslations($questions);

        $questions = $questions->toArray();
        shuffle($questions);

        $questions = array_map(function ($question) use ($category) {
            $question['category'] = $category->name;
            shuffle($question['question_answers']);

            return $question;
        }, $questions);

        return Response::json([
            'question' => $questions[0],
            'currentQuestionsCount' => null,
        ]);
    }

    public function saveAnswer($question_id)
    {
        /** @var Question $question */
        $question = Question::findOrFail($question_id);
        if ($question->app_id != user()->app_id) {
            app()->abort(403);
        }

        $answer_ids = Input::get('answer_ids');

        $isCorrect = $question->isCorrect($answer_ids);

        $entry = LearnBoxCard::where('user_id', user()->id)
            ->where('type', LearnBoxCard::TYPE_QUESTION)
            ->where('foreign_id', $question_id)
            ->first();
        if (! $entry) {
            $entry = new LearnBoxCard();
            $entry->user_id = user()->id;
            $entry->type = LearnBoxCard::TYPE_QUESTION;
            $entry->foreign_id = $question_id;
            $entry->userdata = ['note_back' => '', 'note_front' => ''];
            $entry->box = 0;
        }
        $entry->box_entered_at = date('Y-m-d H:i:s');

        if ($isCorrect) {
            $entry->box += 1;
        } else {
            $entry->box = 0;
        }
        $entry->box = max(0, min(4, $entry->box));

        $this->questionsEngine->updateLearnBoxCards(collect([$entry]), user());

        if ($question->type == Question::TYPE_MULTIPLE_CHOICE) {
            $correctAnswers = $question->questionAnswers()
                ->where('correct', 1)
                ->select(DB::raw('question_answers.id as id'))
                ->pluck('id');
            $correctAnswers = $correctAnswers->map(function ($answer) {
                return (int) $answer;
            });

            return Response::json([
                'correct_answer_id' => $correctAnswers,
                'feedback' => QuestionAnswer::whereIn('id', $answer_ids)
                    ->get()
                    ->pluck('feedback', 'id'),
                'result' => $isCorrect,
            ]);
        } else {
            $feedback = QuestionAnswer::where('id', $answer_ids)
                ->get()
                ->pluck('feedback', 'id');
            // Look for the correct result
            foreach ($question->questionAnswers as $otherQuestionAnswer) {
                // Return the json with the id of the correct answer
                if ($otherQuestionAnswer->correct) {
                    if (! $feedback[$answer_ids]) {
                        $feedback = [$otherQuestionAnswer->id => $otherQuestionAnswer->feedback];
                    }
                    $response = [
                        'correct_answer_id' => $otherQuestionAnswer->id,
                        'feedback' => $feedback,
                        'result' => $isCorrect,
                    ];

                    return Response::json($response);
                }
            }
        }
    }

    public function checkAnswer($question_id)
    {
        /** @var Question $question */
        $question = Question::findOrFail($question_id);
        if ($question->app_id != user()->app_id) {
            app()->abort(403);
        }

        $answer_ids = Input::get('answer_ids');

        $isCorrect = $question->isCorrect($answer_ids);

        if ($question->type == Question::TYPE_MULTIPLE_CHOICE) {
            $correctAnswers = $question->questionAnswers()
                ->where('correct', 1)
                ->pluck('question_answers.id');
            $correctAnswers = $correctAnswers->map(function ($answer) {
                return (int) $answer;
            });

            return Response::json([
                'correct_answer_id' => $correctAnswers,
                'feedback' => QuestionAnswer::whereIn('id', $answer_ids)
                    ->pluck('feedback', 'id'),
                'result' => $isCorrect,
            ]);
        } else {
            $feedback = QuestionAnswer::where('id', $answer_ids)
                ->pluck('feedback', 'id');
            // Look for the correct result
            foreach ($question->questionAnswers as $otherQuestionAnswer) {
                if (! $feedback[$answer_ids]) {
                    $feedback = $otherQuestionAnswer->pluck('feedback', 'id');
                }
                // Return the json with the id of the correct answer
                if ($otherQuestionAnswer->correct) {
                    $response = [
                        'correct_answer_id' => $otherQuestionAnswer->id,
                        'feedback' => $feedback,
                        'result' => $isCorrect,
                    ];

                    return Response::json($response);
                }
            }
        }
    }

    public function statsData()
    {
        $categories = user()->getQuestionCategories();
        $questions = Question::whereIn('category_id', $categories->pluck('id'))
            ->where('type', '!=', Question::TYPE_INDEX_CARD)
            ->select(['id', 'category_id'])->get();
        $learnBoxes = LearnBoxCard::where('user_id', user()->id)->where('type', LearnBoxCard::TYPE_QUESTION)->select('box', 'foreign_id')->get();

        return Response::json([
            'questions' => $questions,
            'learnBoxes' => $learnBoxes,
        ]);
    }

    /**
     * Saves the data from the local learning module.
     * This can happen both in real-time,
     * and after the user comes back online
     * to sync his offline progress.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function saveData(Request $request)
    {
        $user = user();
        $data = $request->get('data');
        // FIXME: validate question IDs
        $questionIds = array_map(function ($entry) {
            // id is the id of the question, not the id of the learn box card
            return $entry['id'];
        }, $data);
        /** @var Collection $learnBoxCards */
        $learnBoxCards = LearnBoxCard::where('user_id', $user->id)
            ->where('type', LearnBoxCard::TYPE_QUESTION)
            ->whereIn('foreign_id', $questionIds)
            ->get()
            ->keyBy('foreign_id');
        $updated = [];
        foreach ($data as $entry) {
            if ($learnBoxCard = $learnBoxCards->get($entry['id'])) {
                if ($learnBoxCard['box_entered_at'] < $entry['box_entered_at']) {
                    $learnBoxCard->box = $entry['box'];
                    $learnBoxCard->box_entered_at = $entry['box_entered_at'];
                    $learnBoxCard->save();

                    $updated[] = $entry['id'];
                }
            } else {
                $learnBoxCard = new LearnBoxCard();
                $learnBoxCard->user_id = $user->id;
                $learnBoxCard->type = LearnBoxCard::TYPE_QUESTION;
                $learnBoxCard->foreign_id = $entry['id'];
                $learnBoxCard->box = $entry['box'];
                $learnBoxCard->box_entered_at = $entry['box_entered_at'];
                $learnBoxCard->save();

                $updated[] = $entry['id'];
            }
        }

        // we count the number of cards that were touched each day per user
        $cardCount = LearnBoxCardUserDailyCount::firstOrNew([
            'user_id' => $user->id,
            'date' => Carbon::now()->format('Y-m-d'),
        ]);
        foreach ($updated as $updatedCardId) {
            $cacheId = 'learnboxcard-updated-'.$user->id.'-'.$updatedCardId;
            // only if we didn't count that card yet
            if (!Cache::driver(config('cache.persistent'))->get($cacheId)) {
                $cardCount->count += 1;
                Cache::driver(config('cache.persistent'))->put($cacheId, true, Carbon::now()->secondsUntilEndOfDay());
            }
        }
        $cardCount->save();

        return Response::json($updated);
    }
}
