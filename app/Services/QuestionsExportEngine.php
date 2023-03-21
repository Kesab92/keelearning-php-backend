<?php

namespace App\Services;

use App\Exceptions\Sentry;
use App\Exports\QuestionsExport;
use App\Imports\Excel\DefaultHeadingRowImport;
use App\Imports\QuestionsImport;
use App\Models\App;
use DB;
use Excel;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class QuestionsExportEngine
{
    private string $_lastError = '';

    public function export(App $app, $from, $to, $questions)
    {
        $entries = [];

        $questions = $questions->keyBy('id');

        $questionTranslations = DB::table('question_translations')
            ->whereIn('question_id', $questions->keys())
            ->whereIn('question_translations.language', [$from, $to])
            ->select(['question_translations.question_id', 'question_translations.title', 'question_translations.language'])
            ->get()
            ->groupBy('question_id');

        $answerTranslations = DB::table('question_answer_translations')
            ->join('question_answers', 'question_answers.id', 'question_answer_translations.question_answer_id')
            ->whereIn('question_id', $questions->keys())
            ->whereIn('question_answer_translations.language', [$from, $to])
            ->select(['question_answers.id', 'question_answers.question_id', 'question_answer_translations.content', 'question_answer_translations.feedback', 'question_answer_translations.language'])
            ->get()
            ->groupBy('id');

        foreach ($questions as $question) {
            $fromTranslation = $questionTranslations->get($question->id)->where('language', $from)->first();
            $toTranslation = $questionTranslations->get($question->id)->where('language', $to)->first();
            $entries[] = [
                $question->id,
                'question:title',
                $fromTranslation ? $fromTranslation->title : '',
                $toTranslation ? $toTranslation->title : '',
            ];
            foreach ($question->questionAnswers as $answer) {
                $fromTranslation = $answerTranslations->get($answer->id)->where('language', $from)->first();
                $toTranslation = $answerTranslations->get($answer->id)->where('language', $to)->first();
                $entries[] = [
                    $answer->id,
                    'answer:content:'.($answer->correct ? 'correct' : 'wrong'),
                    $fromTranslation ? $fromTranslation->content : '',
                    $toTranslation ? $toTranslation->content : '',
                ];
                if ($answer->feedback) {
                    $entries[] = [
                        $answer->id,
                        'answer:feedback:'.($answer->correct ? 'correct' : 'wrong'),
                        $fromTranslation ? $fromTranslation->feedback : '',
                        $toTranslation ? $toTranslation->feedback : '',
                    ];
                }
            }
        }

        $headers = [
            'ID',
            'Type',
            strtoupper($from).' - nicht editieren (Ausgangssprache)',
            strtoupper($to).' - hier Änderungen vornehmen (Zielsprache)',
        ];
        $data = [
            'app'     => $app,
            'from'    => $from,
            'to'      => $to,
            'headers' => $headers,
            'entries' => $entries,
        ];

        return new QuestionsExport($headers, $data);
    }

    public function getImportData(UploadedFile $file, $appId)
    {
        // [0][0] is to access the first sheet which we are interested in
        $headings = (new DefaultHeadingRowImport)->toArray($file)[0][0];
        $data = [];
        $data['from'] = $this->getLanguageFromHeading($headings[2], $appId);
        $data['to'] = $this->getLanguageFromHeading($headings[3], $appId);
        $data['entries'] = [];
        $data['lastError'] = null;

        $appLanguages = App::getLanguagesById($appId);
        if (!$data['to'] || !in_array($data['to'], $appLanguages)) {
            $this->_lastError = 'Die Zielsprache ist für diese App nicht gültig (' . $headings[3] . ')';
            return false;
        }

        return $data;
    }

    /**
     * The heading contains text (for example "DE_FORMAL - nicht editieren (Ausgangssprache)")
     * We extract the actual language here
     *
     * @param string $heading
     * @return string
     */
    private function getLanguageFromHeading($heading, $appId) {
        $appLanguages = App::getLanguagesById($appId);
        // Sort by length of language code, because we match the first available language and it could misinterpret "de_formal" for "de" otherwise
        usort($appLanguages, function($a, $b) {
            return strlen($b) - strlen($a);
        });
        foreach($appLanguages as $lc) {
            if(Str::startsWith($heading, $lc)) {
                return $lc;
            }
        }
        return null;
    }

    public function import(UploadedFile $file, $appId) {
        $data = $this->getImportData($file, $appId);
        if (! $data) {
            return false;
        }

        try {
            Excel::import(new QuestionsImport($appId, $data), $file);
        } catch (\Exception $e) {
            \Sentry::captureException($e);
            $this->_lastError = $e->getMessage();

            return false;
        }

        return true;
    }

    /**
     * @return string
     */
    public function getLastError()
    {
        return $this->_lastError;
    }
}
