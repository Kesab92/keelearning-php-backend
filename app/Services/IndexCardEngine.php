<?php

namespace App\Services;

use App\Models\IndexCard;
use App\Models\LearnBoxCard;
use App\Models\User;
use Illuminate\Support\Collection;

class IndexCardEngine
{
    public function getAllCards($appId)
    {
        return IndexCard::where('app_id', $appId)->get();
    }

    public function updateLearnBoxCards(Collection $entries, $user)
    {
        /** @var Collection $existingEntries */
        $existingEntries = LearnBoxCard
            ::where('user_id', $user->id)
            ->where('type', LearnBoxCard::TYPE_INDEX_CARD)
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
                    'type' => LearnBoxCard::TYPE_INDEX_CARD,
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

    public function getSavedata($userId)
    {
        $existingCards = LearnBoxCard::where('user_id', $userId)->where('type', LearnBoxCard::TYPE_INDEX_CARD)->get()->keyBy('foreign_id');
        $user = User::find($userId);
        $newCards = IndexCard::whereNotIn('id', $existingCards->keys())->where('app_id', $user->app_id)->get();

        foreach ($newCards as $newCard) {
            $learnBoxCard = new LearnBoxCard();

            // This is a bit of a hack, but we need a fairly quick solution here and in the frontend we depend on the id being set
            // When saving the cards, we check if the id is negative and create it then with a proper id
            $learnBoxCard->id = -1 * $newCard->id;

            $learnBoxCard->user_id = $user->id;
            $learnBoxCard->type = LearnBoxCard::TYPE_INDEX_CARD;
            $learnBoxCard->foreign_id = $newCard->id;
            $learnBoxCard->box = 0;
            $learnBoxCard->box_entered_at = date('Y-m-d H:i:s');
            $learnBoxCard->userdata = ['note_back'=>'', 'note_front'=>''];

            // We don't save here for performance reasons. Saving is done when the user actually wants to save the cards

            $existingCards->push($learnBoxCard);
        }

        return $existingCards;
    }
}
