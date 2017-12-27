<?php

namespace bizley\podium\api\tests;

use bizley\podium\api\components\Member;
use bizley\podium\api\repositories\Member as MemberRepo;

class MemberTest extends TestComponent
{
    protected static $inputErrorData = ['username' => null];
    protected static $inputSuccessData = ['username' => 'test'];
    protected static $updateSuccessData = ['username' => 'testUpdated'];
    protected static $selectQuery = ['username', 'slug', 'status'];
    protected static $insertCondition = ['username' => 'test'];
    protected static $updateCondition = ['username' => 'testUpdated'];
    protected static $addedRepo = [
        'username' => 'test',
        'slug' => 'test',
        'status' => '0',
    ];
    protected static $updatedRepo = [
        'username' => 'testUpdated',
        'slug' => 'testupdated',
        'status' => '0',
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
