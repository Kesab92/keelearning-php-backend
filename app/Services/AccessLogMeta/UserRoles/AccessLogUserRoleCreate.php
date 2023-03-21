<?php

namespace App\Services\AccessLogMeta\UserRoles;

use App\Models\UserRole;
use App\Services\AccessLogMeta\AccessLogMeta;

class AccessLogUserRoleCreate implements AccessLogMeta
{
    /**
     * @var null
     */
    protected $userRole = null;

    /**
     * AccessLogUserRoleCreate constructor.
     * @param UserRole $userRole
     */
    public function __construct(UserRole $userRole)
    {
        $this->userRole = $userRole;
    }

    /**
     * @return array
     */
    public function getMeta()
    {
        return $this->userRole;
    }

    /**
     * @param $meta
     * @return string
     */
    public static function displayMeta($meta)
    {
        return view('access-logs.types.user-roles.create', [
            'meta' => $meta,
        ]);
    }
}
