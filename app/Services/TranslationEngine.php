<?php

namespace App\Services;

use App\Models\App;
use App\Models\Question;
use App\Models\QuestionAttachment;
use DB;
use Illuminate\Support\Collection;

class TranslationEngine
{
    public function cacheTranslations(&$collection, $lang = null, $appId = null)
    {
        if (! $collection->count()) {
            return;
        }
        if (is_null($lang)) {
            $lang = language($appId);
        }

        $translationModel = $collection->first()->getTranslationModelName();
        $foreignIdColumn = $collection->first()->getForeignIdColumn();
        $ids = $collection->pluck('id')->toArray();

        $translations = (new $translationModel())->whereIn($foreignIdColumn, $ids)
                                                 ->where('language', $lang)
                                                 ->get();
        if (defaultAppLanguage($appId) != $lang) {
            $missingIds = array_diff($ids, $translations->pluck($foreignIdColumn)->toArray());
            if (count($missingIds)) {
                $translations = $translations->merge((new $translationModel())->whereIn($foreignIdColumn, $missingIds)
                                                              ->where('language', defaultAppLanguage($appId))
                                                              ->get());
            }
        }
        $missingIds = array_diff($ids, $translations->pluck($foreignIdColumn)->toArray());
        if (count($missingIds)) {
            $translations = $translations->merge((new $translationModel())->whereIn($foreignIdColumn, $missingIds)
                                                          ->get());
        }

        $collection = $collection->map(function ($object) use ($translations, $foreignIdColumn) {
            if ($translation = $translations->where($foreignIdColumn, $object->id)->first()) {
                $object->cacheTranslation($translation);
            }

            return $object;
        });
    }

    /**
     * @param Collection|Question[] $questions
     */
    public function attachQuestionAttachments(&$questions)
    {
        if (! $questions->count()) {
            return;
        }
        if (is_array($questions->first()) || ! $questions instanceof \Illuminate\Database\Eloquent\Collection) {
            $attachments = DB::table('question_attachments')
                ->whereIn('question_id', $questions->keys())
                ->select(['id', 'question_id', 'type', 'attachment', 'attachment_url'])
                ->get();
            foreach ($attachments as $attachment) {
                $attachment->url = $attachment->attachment; // TODO: remove once no legacy apps
                $question = $questions[$attachment->question_id];
                if (is_array($question)) {
                    if (! isset($question['attachments'])) {
                        $question['attachments'] = [];
                    }
                    $question['attachments'][] = $attachment;
                } else {
                    if (! isset($question->attachments)) {
                        $question->attachments = [];
                    }
                    $question->attachments[] = $attachment;
                }
            }
        } else {
            $questions->load('attachments');
            $questions->transform(function (Question $question) {
                if ($question->attachments) {
                    $question->attachments->transform(function (QuestionAttachment $attachment) {
                        $attachment->url = $attachment->attachment; // TODO: remove once no legacy apps

                        return $attachment;
                    });
                }

                return $question;
            });
        }
    }

    /**
     * @param Collection $questions
     */
    public function attachQuestionAnswers(&$questions, App $app = null)
    {
        if (! $app) {
            $app = user()->getApp();
        }
        $language = language($app->id);
        $defaultLanguage = $app->getLanguage();
        $answers = DB::table('question_answer_translations')
            ->join('question_answers', 'question_answers.id', 'question_answer_translations.question_answer_id')
            ->whereIn('question_id', $questions->keys())
            ->where(function ($q) use ($language, $defaultLanguage) {
                $q->where('question_answer_translations.language', $language);
                if ($language !== $defaultLanguage) {
                    $q->orWhere('question_answer_translations.language', $defaultLanguage);
                }
            })
            ->select(['question_answers.id', 'question_answers.question_id', 'question_answers.correct', 'question_answer_translations.content', 'question_answer_translations.feedback', 'question_answer_translations.language'])
            ->get();
        foreach ($answers as $answer) {
            $question = $questions[$answer->question_id];
            $answers = [];
            if (isset($question->answers)) {
                $answers = $question->answers;
            }
            if (isset($answers[$answer->id])) {
                // Override the existing answer if this is the user language
                if ($answer->language === $language) {
                    $answers[$answer->id] = $answer;
                }
            } else {
                $answers[$answer->id] = $answer;
            }
            $question->answers = $answers;
        }
    }

    /**
     * @param Collection $questions
     * @param App|null $app
     * @throws \Exception
     */
    public function attachQuestionTranslations($questions, App $app = null)
    {
        if (count($questions) === 0) {
            return $questions;
        }
        if ($app === null) {
            /** @var App $app */
            $app = $questions->first()->app;
        }
        $questions = $questions->keyBy('id');
        $language = language($app->id);
        $defaultLanguage = $app->getLanguage();
        $translations = DB::table('question_translations')
            ->whereIn('question_id', $questions->keys())
            ->where(function ($q) use ($language, $defaultLanguage) {
                $q->where('question_translations.language', $language);
                if ($language !== $defaultLanguage) {
                    $q->orWhere('question_translations.language', $defaultLanguage);
                }
            })
            ->select(['question_translations.question_id', 'question_translations.title', 'question_translations.latex', 'question_translations.language'])
            ->get();
        foreach ($translations as $translation) {
            if (isset($questions[$translation->question_id]->title)) {
                // Override the existing answer if this is the user language
                if ($translation->language === $language) {
                    $questions[$translation->question_id]->title = $translation->title;
                    $questions[$translation->question_id]->latex = $translation->latex;
                }
            } else {
                $questions[$translation->question_id]->title = $translation->title;
                $questions[$translation->question_id]->latex = $translation->latex;
            }
        }

        return $questions;
    }
}
