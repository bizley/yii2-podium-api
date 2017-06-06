<?php

namespace bizley\podium\api\tests;

use bizley\podium\api\components\Member;

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
        'slug' => 'testUpdated',
        'status' => '0',
    ];

    /**
     * @return Member
     */
    protected function api()
    {
        return $this->podium()->member;
    }

    protected function tableName()
    {
        return \bizley\podium\api\repositories\Member::tableName();
    }
}
