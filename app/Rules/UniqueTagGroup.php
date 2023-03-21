<?php

namespace App\Rules;

use App\Models\App;
use App\Models\Tag;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class UniqueTagGroup implements Rule
{
    private App $app;
    private string $field = '';

    /**
     * Create a new rule instance.
     *
     * @param App $app
     */
    public function __construct(App $app)
    {
        $this->app = $app;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value):bool
    {
        $this->field = $attribute;

        $tagIds = Tag
            ::where('app_id', $this->app->id)
            ->whereIn('id', $value)
            ->pluck('id');
        $tagGroupIds = DB::table('tag_groups')
            ->join('tags', 'tag_groups.id', 'tags.tag_group_id')
            ->where('tag_groups.can_have_duplicates', false)
            ->whereIn('tags.id', $tagIds)
            ->select('tag_groups.id')
            ->pluck('tag_groups.id');

        if ($tagGroupIds->count() != $tagGroupIds->unique()->count()) {
            return false;
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message():string
    {
        return 'Invalid parameter "'.$this->field.'". No multiple entries for exclusive groups allowed.';
    }
}
