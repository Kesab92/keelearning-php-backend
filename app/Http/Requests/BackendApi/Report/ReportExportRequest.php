<?php

namespace App\Http\Requests\BackendApi\Report;

use App\Models\Tag;
use Illuminate\Foundation\Http\FormRequest;

class ReportExportRequest extends FormRequest
{
    public function rules()
    {
        return [
            'settings' => 'required|array|min:1',
            'settings.*' => 'required',
            'tags' => 'array',
            'tags.*' => function ($attribute, $value, $fail) {
                if ($value != -1) {
                    $tagExists = Tag::where('app_id', appId())->where('id', $value)->exists();
                    if (!$tagExists) {
                        $fail('The ' . $attribute . ' is invalid.');
                    }
                }
            },
        ];
    }

    protected function prepareForValidation() {
        $tags = $this->tags ? explode(',', $this->tags) : [];
        $this->merge([
            'settings' => explode(',', $this->settings),
            'tags' => $tags,
        ]);
    }
}
