<?php

namespace App\Models;

use App\Traits\TagRights;
use Storage;

/**
 * App\Models\Category.
 *
 * @property-read \App\Models\App $app
 * @property-read \App\Models\Categorygroup $categorygroup
 * @property int $id
 * @property int $app_id
 * @property string $name
 * @property int $points
 * @property bool $active
 * @property int $categorygroup_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Category ofApp($app_id)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Category whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Category whereAppId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Category whereName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Category whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Category whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Category whereActive($value)
 * @mixin \Eloquent
 * @property string|null $cover_image
 * @property string|null $cover_image_url
 * @property string|null $category_icon
 * @property string|null $category_icon_url
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Question[] $allQuestions
 * @property-read int|null $all_questions_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\CategoryTranslation[] $allTranslationRelations
 * @property-read int|null $all_translation_relations_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\CategoryTranslation[] $category_translation
 * @property-read int|null $category_translation_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Competition[] $competitions
 * @property-read int|null $competitions_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\GameRound[] $gameRounds
 * @property-read int|null $game_rounds_count
 * @property-read mixed $icon_url
 * @property-read mixed $image_url
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\CategoryHider[] $hiders
 * @property-read int|null $hiders_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\IndexCard[] $indexCards
 * @property-read int|null $index_cards_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Question[] $questions
 * @property-read int|null $questions_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\SuggestedQuestion[] $suggestedQuestions
 * @property-read int|null $suggested_questions_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Tag[] $tags
 * @property-read int|null $tags_count
 * @method static \Illuminate\Database\Eloquent\Builder|Category newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Category newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Category query()
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereCategoryIcon($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereCategoryIconUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereCategorygroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereCoverImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereCoverImageUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category wherePoints($value)
 * @mixin IdeHelperCategory
 */
class Category extends KeelearningModel
{
    use \App\Traits\Duplicatable;
    use \App\Traits\Saferemovable;
    use \App\Traits\Translatable;
    use TagRights;

    public $translated = ['name'];

    protected $appends = [
        'icon_url',
        'image_url',
    ];

    public function app()
    {
        return $this->belongsTo(App::class);
    }

    public function categorygroup()
    {
        return $this->belongsTo(Categorygroup::class);
    }

    public function category_translation()
    {
        return $this->hasMany(CategoryTranslation::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class)->withTimestamps();
    }

    public function questions()
    {
        return $this->hasMany(Question::class)
            ->withoutIndexCards();
    }

    public function allQuestions()
    {
        return $this->hasMany(Question::class);
    }

    public function gameRounds()
    {
        return $this->hasMany(GameRound::class);
    }

    public function competitions()
    {
        return $this->hasMany(Competition::class);
    }

    public function suggestedQuestions()
    {
        return $this->hasMany(SuggestedQuestion::class);
    }

    public function indexCards()
    {
        return $this->hasMany(IndexCard::class);
    }

    public function hiders()
    {
        return $this->hasMany(CategoryHider::class);
    }

    /**
     * Limits the query to the scope of categories of the app with the given id.
     *
     * @param $query
     * @param $appId
     * @return mixed
     */
    public function scopeOfApp($query, $appId)
    {
        return $query->where('app_id', '=', $appId);
    }

    private function deletePhysicalImage($path)
    {
        if(Category::where('category_icon', $path)->orWhere('cover_image', $path)->count() === 0) {
            Storage::delete($path);
        }
    }

    public function deleteImage()
    {
        $imagePath = $this->cover_image;
        if (! $imagePath) {
            return;
        }
        $this->cover_image = null;
        $this->cover_image_url = null;
        $this->save();
        $this->deletePhysicalImage($imagePath);
    }

    public function deleteIcon()
    {
        $imagePath = $this->category_icon;
        if (! $imagePath) {
            return;
        }
        $this->category_icon = null;
        $this->category_icon_url = null;
        $this->save();
        $this->deletePhysicalImage($imagePath);
    }

    public function isVisibleForScope($scope)
    {
        foreach ($this->hiders as $hider) {
            if ($hider->scope == $scope) {
                return false;
            }
        }

        return true;
    }

    // TODO: remove after upgrade to new frontend
    public function getIconUrlAttribute()
    {
        return convertPathToLegacy($this->category_icon);
    }

    // TODO: remove after upgrade to new frontend
    public function getImageUrlAttribute()
    {
        return convertPathToLegacy($this->cover_image);
    }
}
