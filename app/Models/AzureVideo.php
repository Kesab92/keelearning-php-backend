<?php

namespace App\Models;

use App\Traits\Duplicatable;

/**
 * App\Models\AzureVideo
 *
 * @property int $id
 * @property int $app_id
 * @property int $progress
 * @property int $status
 * @property string|null $finished_at
 * @property string|null $job_id
 * @property string|null $input_asset_id
 * @property string|null $output_asset_id
 * @property string|null $streaming_url
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\App $app
 * @method static \Illuminate\Database\Eloquent\Builder|AzureVideo newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AzureVideo newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AzureVideo query()
 * @method static \Illuminate\Database\Eloquent\Builder|AzureVideo whereAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AzureVideo whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AzureVideo whereFinishedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AzureVideo whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AzureVideo whereInputAssetId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AzureVideo whereJobId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AzureVideo whereOutputAssetId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AzureVideo whereProgress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AzureVideo whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AzureVideo whereStreamingUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AzureVideo whereUpdatedAt($value)
 * @mixin IdeHelperAzureVideo
 */
class AzureVideo extends KeelearningModel
{
    use Duplicatable;

    const STATES = [
        'Queued'     => 0,
        'Scheduled'  => 1,
        'Processing' => 2,
        'Finished'   => 3,
        'Error'      => 4,
        'Canceled'   => 5,
        'Canceling'  => 6,
    ];

    public function app()
    {
        return $this->belongsTo(App::class);
    }

    public function subtitles()
    {
        return $this->hasMany(AzureVideoSubtitle::class, 'azure_video_output_asset_id', 'output_asset_id');
    }
}
