<?php
namespace App\Transformers\BackendApi\DirectMessages;

use App\Models\User;
use App\Models\DirectMessage;
use App\Transformers\AbstractTransformer;

class DirectMessageListTransformer extends AbstractTransformer
{

    public function transform($model)
    {
        if (! $model) {
            return;
        }

        $sender = $model->sender;

        return [
            'body' => $model->body,
            'updated_at' => $model->updated_at->format('d.m.Y H:i:s'),
            'sender_id' => $model->sender_id,
            'senderName' => $sender ? $sender->username : 'Gelöschter User',
            'senderIsSuperAdmin' => $sender ? $sender->isSuperAdmin() : false,
            'senderIsDummy' => $sender ? $sender->is_dummy : true,
        ];
    }
}

