<?php

namespace App\Services\AccessLogMeta;

use App\Models\Tag;
use App\Models\User;

class AccessLogUserPasswordReset implements AccessLogMeta
{
    private $user;
    private $tagUpdates;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * @return array
     */
    public function getMeta()
    {
        return [
            'user_id' => $this->user->id,
        ];
    }

    /**
     * @param $meta
     * @return string
     */
    public static function displayMeta($meta)
    {
        return view('access-logs.types.passwordreset', [
            'meta' => $meta,
            'user' => User::find($meta['user_id']),
        ]);
    }
}
