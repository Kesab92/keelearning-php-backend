<?php

namespace App\Services;

use App\Models\App;
use App\Models\Question;
use App\Models\QuestionTranslation;
use DB;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class QuestionSearch
{
    public function find($appId, $query, $filters = [], $selectedCategory = 0)
    {
        $questions = Question::query();
        $this->setBaseQuestionQuery($questions, $appId, $selectedCategory, $filters);

        if ($query) {
            $matchingTitles = DB::table('question_translations')
                ->join('questions', 'questions.id', '=', 'question_translations.question_id')
                ->select('questions.id')
                ->whereRaw('question_translations.title LIKE ?', '%'.escapeLikeInput($query).'%');
            $this->setBaseQuestionQuery($matchingTitles, $appId, $selectedCategory, $filters);
            $matchingAnswerContents = DB::table('question_answer_translations')
                ->join('question_answers', 'question_answer_translations.question_answer_id', '=', 'question_answers.id')
                ->join('questions', 'questions.id', '=', 'question_answers.question_id')
                ->select('questions.id')
                ->whereRaw('question_answer_translations.content LIKE ?', '%'.escapeLikeInput($query).'%');

            $this->setBaseQuestionQuery($matchingAnswerContents, $appId, $selectedCategory, $filters);

            // We want to search for a question
            $questions->whereIn('questions.id', $matchingTitles->pluck('questions.id')->toArray() + $matchingAnswerContents->pluck('questions.id')->toArray() + [extractHashtagNumber($query)]);
        }

        $questions->leftJoin('question_translations', 'question_translations.question_id', '=', 'questions.id')
            ->select(['questions.*', DB::raw('COUNT(question_translations.id) as existing_translations')])
            ->groupBy('questions.id');

        return $questions;
    }

    private function setBaseQuestionQuery($query, $appId, $selectedCategory, $filters)
    {
        $query->where('app_id', $appId)
            ->where('confirmed', true);

        if ($selectedCategory) {
            $query->where('category_id', $selectedCategory);
        }
        $filters = collect($filters);
        $visibilityFilters = $filters->filter(function($filter) {
            return Str::startsWith($filter, 'visibility_');
        })->map(function($filter) {
            return str_replace('visibility_', '', $filter);
        });
        if($visibilityFilters->contains('-1')) {
            $visibilityFilters = collect([]);
        }
        if($visibilityFilters->count()) {
            $query->whereIn('visible', $visibilityFilters);
        }


        $typeFilters = $filters->filter(function($filter) {
            return Str::startsWith($filter, 'type_');
        })->map(function($filter) {
            return str_replace('type_', '', $filter);
        });
        if($typeFilters->count()) {
            $query->whereIn('type', $typeFilters);
        }
    }

    /**
     * Returns an array with question ids as keys and values being the missing translations for that question.
     *
     * @param $questionIds Collection|array List of question ids
     * @param $appId int
     * @return array
     */
    public function getMissingTranslations($questionIds, $appId)
    {
        /** @var Collection $existingTranslations */
        $existingTranslations = QuestionTranslation::whereIn('question_id', $questionIds)
            ->select(['question_id', DB::raw("GROUP_CONCAT(language SEPARATOR ',') as languages")])
            ->groupBy('question_id')
            ->pluck('languages', 'question_id');
        $availableLanguages = App::getLanguagesById($appId);
        $missingTranslations = [];
        foreach ($questionIds as $questionId) {
            $existingTranslationList = explode(',', $existingTranslations->get($questionId, ''));
            $missingTranslations[$questionId] = array_values(array_diff($availableLanguages, $existingTranslationList));
        }

        return $missingTranslations;
    }
}
