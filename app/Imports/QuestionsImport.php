<?php

namespace App\Imports;

use App\Models\AccessLog;
use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Services\AccessLogEngine;
use App\Services\AccessLogMeta\Questions\AccessLogQuestionUpdate;
use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Date;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class QuestionsImport implements ToArray, WithHeadingRow, WithCalculatedFormulas
{
    private array $data;
    private int $appId;
    private array $questionIds;
    private array $preQuestionData;

    public function __construct($appId, $data)
    {
        $this->data = $data;
        $this->appId = $appId;
        $this->questionIds = [];
    }

    public function array(array $importData)
    {
        $validTypes = [
            'question' => [
                'title',
            ],
            'answer' => [
                'content',
                'feedback',
            ],
        ];

        $entries = [];

        foreach ($importData as $i => $row) {
            $row = array_values($row);
            $row['id'] = $row[0];
            $row['type'] = $row[1];
            $row['from'] = $row[2];
            $row['to'] = $row[3];

            $humanReadableRow = $i + 2;
            // fetched an empty row?
            if ($row['id'] === null) {
                continue;
            }
            $entry = [];

            // invalid ID?
            $entry['id'] = (int) $row['id'];
            if (! $entry['id']) {
                throw new \Exception('Ungültige Daten (ID) in Zeile '.$humanReadableRow);
            }

            // invalid object/attribute definition?
            $type = explode(':', $row['type']);
            if (! in_array(count($type), [2, 3]) ||
                ! in_array($type[0], array_keys($validTypes)) ||
                ! in_array($type[1], $validTypes[$type[0]])
            ) {
                throw new \Exception('Ungültige Daten (Typ) in Zeile '.$humanReadableRow);
            }
            $entry['object'] = $type[0];
            $entry['attribute'] = $type[1];

            // do we have a translation?
            if (! $row['to']) {
                throw new \Exception('Fehlende Übersetzung in Zeile '.$humanReadableRow);
            }
            $entry['translation'] = $row['to'];

            $entries[] = $entry;
        }

        // After all rows have been validated, actually import them
        DB::transaction(function () use ($entries) {
            foreach ($entries as $i => $row) {
                $humanReadableRow = $i + 2;
                if (! $this->importExcelRow($row)) {
                    throw new \Exception('Zeile '.$humanReadableRow.' konnte nicht importiert werden');
                }
            }
            $this->touchQuestions();
            $this->createQuestionUpdateLogs();
        });
    }

    // assumes row has been validated (object and attribute)
    private function importExcelRow($row)
    {
        if ($row['object'] == 'question') {
            $object = Question::where('app_id', $this->appId);
        }
        if ($row['object'] == 'answer') {
            $object = QuestionAnswer::whereHas('question', function ($query) {
                $query->where('app_id', $this->appId);
            });
        }
        $entry = $object->where('id', $row['id'])->first();
        if (! $entry) {
            throw new \Exception('Ungültige ID '.$row['id']);
        }
        if ($row['object'] == 'question') {
            $this->preQuestionData[$row['id']] = AccessLogQuestionUpdate::createQuestionValues($entry);
        }

        /** @var Model $translation */
        $translation = $entry->translationRelation($this->data['to'])->first();
        if (! $translation) {
            $modelName = $entry->getTranslationModelName();
            $translation = new $modelName();
            $translation->{$entry->getForeignIdColumn()} = $row['id'];
            $translation->language = $this->data['to'];
        }
        $translation->{$row['attribute']} = $row['translation'];
        if ($translation->isDirty()) {
            if ($row['object'] == 'question') {
                $this->questionIds[$row['id']] = true;
            } elseif ($row['object'] == 'answer') {
                $this->questionIds[$entry->question_id] = true;
            }
        }
        $translation->save();

        return true;
    }

    /**
     * Makes sure to set the updated_at timestamp for all questions.
     */
    private function touchQuestions()
    {
        $questionIds = array_keys($this->questionIds);
        if (! count($questionIds)) {
            return;
        }
        DB::table('questions')
            ->whereIn('id', $questionIds)
            ->where('app_id', $this->appId)
            ->update(['updated_at' => Date::now()]);
    }

    private function createQuestionUpdateLogs()
    {
        $questionIds = array_keys($this->questionIds);
        if (! count($questionIds)) {
            return;
        }
        $questionData = Question::whereIn('id', $questionIds)
            ->where('app_id', $this->appId)
            ->get()
            ->keyBy('id');
        /** @var AccessLogEngine $accessLogEngine */
        $accessLogEngine = app(AccessLogEngine::class);
        foreach ($questionIds as $questionId) {
            if (! isset($this->preQuestionData[$questionId])) {
                continue;
            }
            $oldQuestion = $this->preQuestionData[$questionId];
            $newQuestion = $questionData->get($questionId);
            if (! $newQuestion) {
                continue;
            }
            $accessLogEngine->log(
                AccessLog::ACTION_QUESTION_UPDATE,
                new AccessLogQuestionUpdate($newQuestion, $oldQuestion)
            );
        }
    }
}
