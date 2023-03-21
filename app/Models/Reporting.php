<?php

namespace App\Models;

use App\Services\ReportingEngine;
use App\Traits\Saferemovable;
use Carbon\Carbon;

/**
 * App\Models\Reporting
 *
 * @property int $id
 * @property int $app_id
 * @property array $tag_ids
 * @property array $group_ids
 * @property array $category_ids
 * @property array $emails
 * @property string $interval
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\App $app
 * @method static \Illuminate\Database\Eloquent\Builder|Reporting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Reporting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Reporting query()
 * @method static \Illuminate\Database\Eloquent\Builder|Reporting whereAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Reporting whereCategoryIds($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Reporting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Reporting whereEmails($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Reporting whereGroupIds($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Reporting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Reporting whereInterval($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Reporting whereTagIds($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Reporting whereUpdatedAt($value)
 * @mixin IdeHelperReporting
 */
class Reporting extends KeelearningModel
{
    use Saferemovable;

    protected $casts = [
        'tag_ids' => 'array',
        'emails' => 'array',
        'category_ids' => 'array',
    ];

    const TYPE_QUIZ = 1;
    const TYPE_USERS = 2;

    public function app()
    {
        return $this->belongsTo(\App\Models\App::class);
    }

    /**
     * Checks if the reporting should be sent today.
     *
     * @param null $today
     * @return bool
     */
    public function isDue($today = null)
    {
        if (is_null($today)) {
            $today = Carbon::now();
        }
        switch ($this->interval) {
            case ReportingEngine::INTERVAL_1W:
                return $today->dayOfWeek === 1;
            case ReportingEngine::INTERVAL_2W:
                return $today->dayOfWeek === 1 && $today->weekOfYear % 2 === 1;
            case ReportingEngine::INTERVAL_1M:
                return $today->day === 1;
            case ReportingEngine::INTERVAL_3M:
                return $today->day === 1 && $today->month % 3 === 1;
            case ReportingEngine::INTERVAL_6M:
                return $today->day === 1 && $today->month % 6 === 1;
            case ReportingEngine::INTERVAL_1Y:
                return $today->day === 1 && $today->month === 1;
            default:
                return false;
        }
    }
}
