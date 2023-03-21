<?php

namespace App\Traits;

use App\Models\User;
use App\Services\AppSettings;
use Auth;


/**
 * Trait PersonalData
 */
trait PersonalData
{
    private ?AppSettings $appSettings = null;
    private bool $showPersonalData = false;
    private bool $showEmails = false;

    /**
     * Fills the $showPersonalData and $showEmails props based on
     * the provided $right, eg `users` for checking against
     * `users-privatedata` and `users-showemails`.
     * Call this method from the constructor.
     *
     * @param string $right
     * @return void
     */
    private function personalDataRightsMiddleware(string $right): void
    {
        $this->middleware(function ($request, $next) use ($right) {
            $this->checkPersonalDataRights($right, Auth::user());
            return $next($request);
        });
    }

    private function checkPersonalDataRights(string $right, User $user): void
    {
        if (!$this->appSettings) {
            $this->appSettings = app(AppSettings::class);
        }
        if ($user->isSuperAdmin()) {
            $this->showPersonalData = true;
            $this->showEmails = true;
        } else {
            $this->showPersonalData = !$this->appSettings->getValue('hide_personal_data') && $user->hasRight($right . '-personaldata');
            $this->showEmails = $this->showPersonalData && !$this->appSettings->getValue('hide_emails_backend') && $user->hasRight($right . '-showemails');
        }

    }
}
