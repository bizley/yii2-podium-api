<?php

namespace bizley\podium\api\tests;

use bizley\podium\api\components\Member;
use bizley\podium\api\repositories\Member as MemberRepo;

class MemberTest extends TestComponent
{
    public $data = [
        'inputErrorData' => ['username' => null],
        'inputSuccessData' => ['username' => 'test'],
        'updateSuccessData' => ['username' => 'testUpdated'],
        'selectQuery' => ['username', 'slug', 'status'],
        'insertCondition' => ['username' => 'test'],
        'updateCondition' => ['username' => 'testUpdated'],
        'addedRepo' => [
            'username' => 'test',
            'slug' => 'test',
            'status' => '0',
        ],
        'updatedRepo' => [
            'username' => 'testUpdated',
            'slug' => 'testupdated',
            'status' => '0',
        ],
    ];

    /**
     * @param bool $clear
     * @return Member
     */
    protected function repo($clear = false)
    {
        return $this->podium()->member->getRepo($clear);
    }

    protected function tableName()
    {
        return MemberRepo::tableName();
    }
}
