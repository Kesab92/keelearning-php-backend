<?php


namespace App\Services\AccessLogMeta\UserRoles;

use App\Models\userRole;
use App\Services\AccessLogMeta\AccessLogMeta;

class AccessLogUserRoleUpdate implements AccessLogMeta
{

    /**
     * @var array
     */
    protected  $rightsGiven = [];
    protected  $rightsRemoved = [];


    public function __construct(array $oldRole, array $newRole)
    {
        foreach ($newRole as $key => $value){
            if($key=='rights'){
                $this->rightsGiven = $newRole[$key]->diff($oldRole[$key])->values();
                $this->rightsRemoved = $oldRole[$key]->diff($newRole[$key])->values();
            }
        }
        $this->userRoleId = $newRole['id'];
    }

    /**
     * @return array
     */
    public function getMeta()
    {
        return  [
            'userRoleId' => $this->userRoleId,
            'rightsGiven' => $this->rightsGiven,
            'rightsRemoved' => $this->rightsRemoved,
        ];
    }

    /**
     * @param $meta
     * @return string
     */
    public static function displayMeta($meta)
    {
        return view('access-logs.types.user-roles.update', [
            'meta' => $meta
        ]);
    }
    /**
     * @return boolean
     */
    public function hasDifferences()
    {
        return count($this->rightsGiven) || count($this->rightsRemoved);
    }
}
