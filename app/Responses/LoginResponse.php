<?php

namespace App\Responses;

use App\Models\User;

class LoginResponse extends KeelearningResponse {
    protected User $user;
    protected string $token;
    protected ?int $deletedTokens;

    public function __construct(User $user, string $token, ?int $deletedTokens = null)
    {
        $this->user = $user;
        $this->token = $token;
        $this->deletedTokens = $deletedTokens;
    }

    public function toArray()
    {
        return [
            'active'               => $this->user->active,
            'avatar'               => $this->user->avatar_url,
            'deleted_tokens'       => $this->deletedTokens,
            'displayname'          => $this->user->displayname,
            'email'                => $this->user->email,
            'force_password_reset' => $this->user->force_password_reset,
            'id'                   => $this->user->id,
            'is_admin'             => $this->user->is_admin || $this->user->isSuperAdmin(),
            'language'             => $this->user->language,
            'name'                 => $this->user->username,
            'success'              => true,
            'token'                => $this->token,
            'tos_accepted'         => $this->user->tos_accepted,
            'isTmpAccount'         => $this->user->isTmpAccount(),
        ];
    }
}
