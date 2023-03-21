<?php

namespace App\Traits;

use App\Models\App;
use App\Models\AppProfile;
use App\Models\MailTemplate;
use App\Models\User;
use App\Services\AppSettings;

/**
 * Trait CustomMail.
 */
trait CustomMail
{
    protected ?App $app;
    protected array $data = [];
    // forces an app profile for the replacement variables only
    protected ?AppProfile $forceAppProfile;
    protected ?User $recipient = null;
    private ?MailTemplate $template = null;
    public static array $baseTags = [
        'appname',
        'backend-url',
        'contact-mail',
        'contact-phone',
        'email',
        'tos',
        'url-login',
        'url',
        'username',
        'realname-or-username',
    ];

    public static function getTags()
    {
        return array_merge(self::$baseTags, self::$availableTags, self::$requiredTags);
    }

    protected function language()
    {
        return $this->recipient ? $this->recipient->getLanguage() : language($this->app->id);
    }

    /**
     * @return AppProfile
     */
    protected function appProfile()
    {
        if (isset($this->forceAppProfile) && $this->forceAppProfile) {
            return $this->forceAppProfile;
        } elseif (isset($this->recipient) && $this->recipient) {
            return $this->recipient->getAppProfile();
        } else {
            return $this->app->getDefaultAppProfile();
        }
    }

    private function getType()
    {
        return substr(get_class(), strrpos(get_class(), '\\') + 1);
    }

    private function getTemplate()
    {
        if ($this->template) {
            return $this->template->setLanguage($this->language());
        }

        return $this->template = MailTemplate::getTemplate($this->getType(), $this->app->id)->setLanguage($this->language());
    }

    private function parseTemplate($template, $stripHtml = false)
    {
        $appSettings = new AppSettings($this->app->id);
        $appProfile = $this->appProfile();

        $hostedAt = $appProfile->app_hosted_at;
        $loginUrl = $hostedAt . '/login';
        if($appSettings->getValue('has_candy_frontend')) {
            $loginUrl = $hostedAt . '/auth/login';
        }

        $replace = [
            'appname'               => $appProfile->getValue('app_name'),
            'backend-url'           => backendPath(),
            'contact-mail'          => $appProfile->getValue('contact_email'),
            'contact-phone'         => $appProfile->getValue('contact_phone'),
            'email'                 => $this->recipient ? $this->recipient->email : '',
            'tos'                   => $appProfile->getValue('email_terms'),
            'url-login'             => $loginUrl,
            'url'                   => $hostedAt,
            'username'              => $this->recipient ? $this->recipient->username : '',
            'realname-or-username'  => $this->recipient ? $this->recipient->getFullName() : '',
        ];
        foreach (self::getTags() as $tag) {
            if (! array_key_exists($tag, $replace)) {
                if (array_key_exists($tag, $this->data)) {
                    if ($stripHtml) {
                        $replace[$tag] = strip_tags($this->data[$tag]);
                    } else {
                        $replace[$tag] = $this->data[$tag];
                    }
                } else {
                    $replace[$tag] = '';
                }
            }
        }

        return str_replace(
            array_map(function ($v) {
                return '%'.$v.'%';
            }, array_keys($replace)),
            array_values($replace),
            $template
        );
    }

    private function getContent()
    {
        return $this->parseTemplate($this->getTemplate()->body);
    }

    public function getTitle()
    {
        return $this->parseTemplate($this->getTemplate()->title, true);
    }

    public function containsTag($tag)
    {
        $tag = '%'.$tag.'%';

        return
            strpos($this->getTemplate()->title, $tag) !== false
            || strpos($this->getTemplate()->body, $tag) !== false;
    }

    public function addTagData(array $newTagData)
    {
        $this->data = array_merge($this->data, $newTagData);
    }

    /**
     * Use this to return a custom data array which can be used in the html.blade.php email template.
     * Right now it's only used to set 'hideEncoding' to true for some emails.
     *
     * @return array
     */
    public function getCustomViewData(): array {
        return [];
    }

    /**
     * Returns an array of variables that are passed to the html.blade.php email template file
     * @return array
     */
    public function getViewData(): array {
        $defaultData = [
            'content' => $this->getContent(),
            'app' => $this->app,
            'language' => $this->language(),
        ];
        if($this->recipient) {
            $defaultData['appProfile'] = $this->recipient->getAppProfile();
        } else {
            $defaultData['appProfile'] = $this->app->getDefaultAppProfile();
        }

        // Take the default data and add (or overwrite) any custom data entries
        return array_merge($defaultData, $this->getCustomViewData());
    }

    /**
     * Build the message.
     *
     * @return self
     */
    public function build()
    {
        return $this->view('mail.html')
            ->text('mail.text')
            ->with($this->getViewData())
            ->subject($this->getTitle());
    }
}
