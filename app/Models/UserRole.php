<?php

namespace App\Models;

use App\Traits\Duplicatable;
use App\Traits\Saferemovable;

/**
 * @mixin IdeHelperUserRole
 */
class UserRole extends KeelearningModel
{
    use Saferemovable;
    use Duplicatable;

    public function scopeOfApp($query, $appId)
    {
        return $query->where('app_id', '=', $appId);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function rights()
    {
        return $this->hasMany(UserRoleRight::class);
    }

    public function hasRight(string $right): bool
    {
        if ($this->is_main_admin) {
            return true;
        }
        return $this->rights->contains('right', $right);
    }
}
