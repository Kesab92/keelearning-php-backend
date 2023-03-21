<?php

namespace App\Models;

use App\Traits\Duplicatable;
use App\Traits\Translatable;

/**
 * App\Models\CertificateTemplate
 *
 * @property int $id
 * @property int $test_id
 * @property string $background_image
 * @property string|null $background_image_url
 * @property string $elements
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $background_image_size
 * @property-read \App\Models\Test $test
 * @method static \Illuminate\Database\Eloquent\Builder|CertificateTemplate newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CertificateTemplate newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CertificateTemplate query()
 * @method static \Illuminate\Database\Eloquent\Builder|CertificateTemplate whereBackgroundImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CertificateTemplate whereBackgroundImageSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CertificateTemplate whereBackgroundImageUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CertificateTemplate whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CertificateTemplate whereElements($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CertificateTemplate whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CertificateTemplate whereTestId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CertificateTemplate whereUpdatedAt($value)
 * @mixin IdeHelperCertificateTemplate
 */
class CertificateTemplate extends KeelearningModel
{
    use Duplicatable;
    use Translatable;

    public $translated = [
        'background_image',
        'background_image_url',
        'background_image_size',
        'elements',
    ];

    public function test()
    {
        return $this->belongsTo(Test::class);
    }
}
