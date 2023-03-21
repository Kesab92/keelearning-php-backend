<?php

namespace App\Http\Requests\PublicApi\User;

use App\Http\Requests\PublicApi\PublicApiStoreFormRequest;
use App\Models\App;
use App\Models\User;
use App\Rules\UniqueEmail;
use App\Rules\UniqueTagGroup;
use App\Rules\UniqueUsername;
use Auth;
use Illuminate\Validation\Rule;

class UserStoreFormRequest extends PublicApiStoreFormRequest {

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */

    public function authorize(): bool
    {
        return true;
    }

    /**
     * Gets the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        $app = Auth::user()->app;
        $appProfile = $app->getDefaultAppProfile();
        $profileNeedsMail = $appProfile->getValue('signup_show_email') && $appProfile->getValue('signup_show_email_mandatory') === 'mandatory';
        $needsMail = !$app->allowMaillessSignup() || $profileNeedsMail || $this->email;
        $availableMetaFields = $app->getUserMetaDataFields(true);
        $uniqueMetaFields = $app->getUniqueMetaFields();

        $validationRules = [
            'username' => [
                'required',
                'min:2',
                'max:255',
                new UniqueUsername(Auth::user()->app)
            ],
            'firstname' => 'min:2|max:255',
            'lastname' => 'min:2|max:255',
            'language' => 'in:' . implode(',', $app->getLanguages()),
            'active' => 'boolean',
            'email' => [
                'email',
                'min:3',
                'max:255',
                new UniqueEmail(Auth::user()->app),
            ],
            'password' => ['required'],
            'tags' => new UniqueTagGroup(Auth::user()->app),
            'tags.*' => Rule::exists('tags', 'id')->where(function ($query) {
                return $query->where('app_id', Auth::user()->app_id);
            }),
            'meta' => function ($attribute, $value, $fail) use ($app, $availableMetaFields, $uniqueMetaFields) {
                foreach ($value as $metakey => $metadata) {
                    if(is_array($metadata)) {
                        $fail('The meta field ' . $metakey . '\'s value can\'t be an array.');
                        continue;
                    }

                    if(!array_key_exists($metakey, $availableMetaFields)) {
                        $fail('The meta field ' . $metakey . ' doesn\'t exist.');
                    }
                    if (in_array($metakey, $uniqueMetaFields)) {
                        $existingUsers = User::getByMetafield($app->id, $metakey, $metadata);
                        if ($existingUsers->count()) {
                            return $fail('The meta field ' . $metakey . ' must be unique.');
                        }
                    }
                }
            },
        ];

        if ($needsMail) {
            $validationRules['email'][] = 'required';
        }

        return $validationRules;
    }

    /**
     * Gets messages for validation rules.
     *
     * @return string[]
     */
    public function messages() {
        $app = App::findOrFail(Auth::user()->app_id);

        return [
            'username.required' => 'Parameter "username" is required.',
            'username.min' => 'Invalid parameter "username". The length has to be at least 2.',
            'username.max' => 'Invalid parameter "username". The length has to be at most 255.',
            'firstname.min' => 'Invalid parameter "firstname". The length has to be at least 2.',
            'firstname.max' => 'Invalid parameter "firstname". The length has to be at most 255.',
            'lastname.min' => 'Invalid parameter "lastname". The length has to be at least 2.',
            'lastname.max' => 'Invalid parameter "lastname". The length has to be at most 255.',
            'language.in' => 'Invalid parameter "language". You can set one of these values: ' . implode(', ', $app->getLanguages()) . '.',
            'email.required' => 'Parameter "email" is required.',
            'email.min' => 'Invalid parameter "email". The length has to be at least 3.',
            'email.max' => 'Invalid parameter "email". The length has to be at most 255.',
            'email.email' => 'Parameter "email" has to be an email address.',
            'active.boolean' => 'Invalid parameter "active". It has to be boolean value.',
        ];
    }

    protected function prepareForValidation() {
        $active = strtolower($this->active);
        if ($active === "true") {
            $active = true;
        }
        if ($active === "false") {
            $active = false;
        }

        $this->merge([
            'username' => utrim($this->username),
            'language' => utrim(strtolower($this->language)),
            'email' => utrim(strtolower($this->email)),
            'firstname' => utrim($this->firstname),
            'lastname' => utrim($this->lastname),
            'active' => $active,
        ]);
    }
}
