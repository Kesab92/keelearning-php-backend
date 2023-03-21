<?php

namespace App\Models;

use App\Services\MorphTypes;
use DateTimeInterface;
use Exception;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\KeelearningModel
 *
 * @method static \Illuminate\Database\Eloquent\Builder|KeelearningModel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|KeelearningModel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|KeelearningModel query()
 * @mixin IdeHelperKeelearningModel
 */
class KeelearningModel extends Model
{
    /**
     * Prepare a date for array / JSON serialization.
     *
     * @param \DateTimeInterface $date
     * @return string
     */
    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    /**
     * Returns the MorphType ID of the current class.
     *
     * @return int
     */
    public function getMorphType(): int
    {
        $morphType = MorphTypes::MAPPING[static::class];
        if (!$morphType) {
            throw new Exception('No MorphType set for '.static::class);
        }
        return $morphType;
    }
}
