<?php

namespace App\Models;

/**
 * App\Models\HelpdeskCategory
 *
 * @property int $id
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $sortIndex
 * @method static \Illuminate\Database\Eloquent\Builder|HelpdeskCategory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|HelpdeskCategory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|HelpdeskCategory query()
 * @method static \Illuminate\Database\Eloquent\Builder|HelpdeskCategory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HelpdeskCategory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HelpdeskCategory whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HelpdeskCategory whereSortIndex($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HelpdeskCategory whereUpdatedAt($value)
 * @mixin IdeHelperHelpdeskCategory
 */
class HelpdeskCategory extends KeelearningModel
{
}
