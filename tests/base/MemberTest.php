<?php

declare(strict_types=1);

namespace bizley\podium\tests\base;

use bizley\podium\api\enums\MemberStatus;
use bizley\podium\api\models\member\Member;
use bizley\podium\tests\DbTestCase;
use yii\data\ActiveDataFilter;

/**
 * Class MemberTest
 * @package bizley\podium\tests\base
 */
class MemberTest extends DbTestCase
{
    /**
     * @var array
     */
    public $fixtures = [
        'podium_member' => [
            [
                'id' => 2,
                'user_id' => '10',
                'username' => 'member2',
                'status_id' => MemberStatus::ACTIVE,
                'created_at' => 1,
                'updated_at' => 1,
            ],
            [
                'id' => 3,
                'user_id' => '11',
                'username' => 'member3',
                'status_id' => MemberStatus::ACTIVE,
                'created_at' => 1,
                'updated_at' => 1,
            ],
        ],
    ];

    public function testGetMemberById(): void
    {
        $member = $this->podium()->member->getMemberById(2);
        $this->assertEquals(2, $member->getId());
    }

    public function testGetMemberByUserId(): void
    {
        $member = $this->podium()->member->getMemberByUserId('10');
        $this->assertEquals(2, $member->getId());
    }

    public function testNonExistingMember(): void
    {
        $this->assertEmpty($this->podium()->member->getMemberById(999));
    }

    public function testGetMembersByFilterEmpty(): void
    {
        $members = $this->podium()->member->getMembers();
        $this->assertEquals(2, $members->getTotalCount());
        $this->assertEquals([2, 3], $members->getKeys());
    }

    public function testGetMembersByFilter(): void
    {
        $filter = new ActiveDataFilter([
            'searchModel' => function () {
                return (new \yii\base\DynamicModel(['id']))->addRule('id', 'integer');
            }
        ]);
        $filter->load(['filter' => ['id' => 3]], '');
        $members = $this->podium()->member->getMembers($filter);
        $this->assertEquals(1, $members->getTotalCount());
        $this->assertEquals([3], $members->getKeys());
    }

    public function testDeleteMember(): void
    {
        $this->assertEquals(1, $this->podium()->category->delete(Member::findOne(2)));
        $this->assertEmpty($this->podium()->member->getMemberById(2));
    }
}
