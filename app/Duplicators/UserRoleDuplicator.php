<?php

namespace App\Duplicators;

class UserRoleDuplicator extends Duplicator
{

    protected $duplicateRelationships = [
        'rights' => [
            'parentRelation' => 'userRole',
        ],
    ];

}
