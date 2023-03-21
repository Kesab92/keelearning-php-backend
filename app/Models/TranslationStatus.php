<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperTranslationStatus
 */
class TranslationStatus extends Model
{
    protected $guarded = [];

    protected $dates = [
        'updated_at',
        'created_at',
        'autotranslation_running_since',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'field_statuses' => 'array',
        'is_outdated' => 'boolean',
    ];

    public function app(): BelongsTo
    {
        return $this->belongsTo(App::class);
    }
}
