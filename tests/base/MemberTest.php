<?php

declare(strict_types=1);

namespace bizley\podium\tests\base;

use bizley\podium\api\enums\MemberStatus;
use bizley\podium\tests\DbTestCase;

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
                'username' => 'member',
                'status_id' => MemberStatus::ACTIVE,
                'created_at' => 1,
                'updated_at' => 1,
            ],
        ],
    ];

    /**
     * @throws \yii\db\Exception
     */
    protected function setUp(): void
    {
        $this->fixturesUp();
    }

    /**
     * @throws \yii\db\Exception
     */
    protected function tearDown(): void
    {
        $this->fixturesDown();
    }

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
}
