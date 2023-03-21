<?php

namespace App\Services;

use App\Models\AzureVideo;
use App\Models\LearnBoxCard;
use App\Models\Question;
use App\Models\QuestionAttachment;
use Illuminate\Support\Collection;
use Storage;

class QuestionsEngine
{
    public function getAllQuestions($appId)
    {
        return Question::where('app_id', $appId)->get();
    }

    public function updateLearnBoxCards(Collection $entries, $user)
    {
        /** @var Collection $existingEntries */
        $existingEntries = LearnBoxCard
            ::where('user_id', $user->id)
            ->where('type', LearnBoxCard::TYPE_QUESTION)
            ->whereIn('foreign_id', $entries->pluck('foreign_id'))
            ->get()
            ->keyBy('foreign_id');

        $newEntries = [];
        foreach ($entries as $data) {
            if ($existingEntry = $existingEntries->get($data['foreign_id'])) {
                $existingEntry->userdata = $data['userdata'];
                $existingEntry->box = $data['box'];
                $existingEntry->box_entered_at = $data['box_entered_at'];
                $existingEntry->save();
            } else {
                $newEntries[] = [
                    'user_id' => $user->id,
                    'type' => LearnBoxCard::TYPE_QUESTION,
                    'foreign_id' => $data['foreign_id'],
                    'userdata' => json_encode($data['userdata']),
                    'box' => $data['box'],
                    'box_entered_at' => $data['box_entered_at'],
                ];
            }
        }
        if ($newEntries) {
            LearnBoxCard::insert($newEntries);
        }
    }

    /**
     * @param $user
     * @param array $categoryIds categories for which to create new cards when they don't exist yet
     * @return Collection
     */
    public function getSavedata($user, $categoryIds)
    {
        /** @var Collection $existingCards */
        $existingCards = LearnBoxCard
            ::where('user_id', $user->id)
            ->where('type', LearnBoxCard::TYPE_QUESTION)
            ->get()
            ->keyBy('foreign_id');

        $newCards = Question
            ::whereNotIn('id', $existingCards->keys())
            ->where('visible', 1)
            ->whereIn('category_id', $categoryIds)
            ->where('app_id', $user->app_id)->get();

        foreach ($newCards as $newCard) {
            $learnBoxCard = new LearnBoxCard();

            // This is a bit of a hack, but we need a fairly quick solution here and in the frontend we depend on the id being set
            // When saving the cards, we check if the id is negative and create it then with a proper id
            $learnBoxCard->id = -1 * $newCard->id;

            $learnBoxCard->user_id = $user->id;
            $learnBoxCard->type = LearnBoxCard::TYPE_QUESTION;
            $learnBoxCard->foreign_id = $newCard->id;
            $learnBoxCard->box = 0;
            $learnBoxCard->box_entered_at = date('Y-m-d H:i:s');

            // We don't save here for performance reasons. Saving is done when the user actually wants to save the cards

            $existingCards->prepend($learnBoxCard, $learnBoxCard->foreign_id);
        }

        return $existingCards;
    }

    public function getCurrentQuestions($user, $categoryId)
    {
        $saveData = $this->getSavedata($user, [$categoryId]);
        $currentQuestions = [];

        foreach ($saveData as $question) {
            if ((strtotime($question->box_entered_at) + LearnBoxCard::LIMITS[$question->box]) <= time()) {
                $currentQuestions[] = $question;
            }
        }

        $questions = Question::with('questionAnswers', 'attachments', 'translationRelation')
                    ->where('category_id', $categoryId)
                    ->where('visible', 1)
                    ->whereIn('id', array_map(function ($question) {
                        return (int) $question->foreign_id;
                    }, $currentQuestions))->get();

        return $questions;
    }

    /**
     * Attaches the correct asset urls to the attachments.
     *
     * @param $question
     * @param $appId
     * @return mixed
     */
    public function formatQuestionForFrontend($question, $appId)
    {
        if (isset($question->attachments)) {
            if ($question->attachments instanceof Collection) {
                $question->attachments = $question->attachments->all();
            }
            $question->attachments = array_map(function ($attachment) use ($question, $appId) {
                if ($attachment->type === QuestionAttachment::ATTACHMENT_TYPE_AUDIO || $attachment->type === QuestionAttachment::ATTACHMENT_TYPE_IMAGE) {
                    $attachment->url = formatAssetURL($attachment->url);
                    $attachment->attachment = formatAssetURL($attachment->attachment);
                    $attachment->attachment_url = formatAssetURL($attachment->attachment_url);
                }
                if ($attachment->type === QuestionAttachment::ATTACHMENT_TYPE_AZURE_VIDEO) {
                    $azureVideo = AzureVideo::where('app_id', $appId)->where('id', $attachment->attachment)->first();
                    if ($azureVideo) {
                        $attachment->attachment = $azureVideo->streaming_url;
                    } else {
                        $attachment->attachment = '';
                    }
                }

                return $attachment;
            }, $question->attachments);

            // Filter azure video attachments without streaming url
            $question->attachments = array_filter($question->attachments, function ($attachment) {
                if ($attachment->type === QuestionAttachment::ATTACHMENT_TYPE_AZURE_VIDEO && ! $attachment->attachment) {
                    return false;
                }

                return true;
            });
        }

        return $question;
    }

    /**
     * Removes all media from the given question attachment.
     *
     * @param QuestionAttachment $questionAttachment
     */
    public function removeMedia(QuestionAttachment $questionAttachment)
    {
        $file = $questionAttachment->attachment;
        $questionAttachment->delete();

        $fileIsUnused = QuestionAttachment::where('attachment', $file)->count() == 0;
        if ($file && $fileIsUnused) {
            Storage::delete($file);
        }
    }
}
