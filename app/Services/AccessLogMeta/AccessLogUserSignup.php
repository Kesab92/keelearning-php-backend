<?php

namespace App\Services\AccessLogMeta;

use App\Models\Tag;
use App\Models\User;

class AccessLogUserSignup implements AccessLogMeta
{
    private $user;

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
        return view('access-logs.types.usersignup', [
            'meta' => $meta,
            'user' => User::find($meta['user_id']),
        ]);
    }
}
