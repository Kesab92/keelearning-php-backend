<?php


namespace App\Services\AccessLogMeta\UserRoles;

use App\Models\UserRole;
use App\Services\AccessLogMeta\AccessLogMeta;

class AccessLogUserRoleDelete implements AccessLogMeta
{
    /**
     * Deleted object
     * @var null
     */
    protected $userRole = null;

    public function __construct(UserRole $userRole)
    {
        $this->userRole = $userRole;
    }

    /**
     * @return array
     */
    public function getMeta()
    {
        return [
             'id' => $this->userRole->id,
             'name' => $this->userRole->name,
        ];
    }

    /**
     * @param $meta
     * @return string
     */
    public static function displayMeta($meta)
    {
        return view('access-logs.types.user-roles.delete', [
            'meta' => $meta
        ]);
    }
}
