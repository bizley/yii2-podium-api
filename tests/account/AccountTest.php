<?php

declare(strict_types=1);

namespace bizley\podium\tests\account;

use bizley\podium\api\base\Account;
use bizley\podium\api\base\NoMembershipException;
use bizley\podium\api\enums\MemberStatus;
use bizley\podium\tests\AccountTestCase;
use bizley\podium\tests\props\UserIdentity;
use Yii;
use yii\db\Exception;

/**
 * Class AccountTest
 * @package bizley\podium\tests\account
 */
class AccountTest extends AccountTestCase
{
    /**
     * @var array
     */
    public $fixtures = [
        'podium_member' => [
            [
                'id' => 10,
                'user_id' => '10',
                'username' => 'member',
                'slug' => 'member',
                'status_id' => MemberStatus::ACTIVE,
                'created_at' => 1,
                'updated_at' => 1,
            ],
        ],
    ];

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->fixturesUp();
    }

    /**
     * @throws Exception
     */
    protected function tearDown(): void
    {
        $this->fixturesDown();
        parent::tearDown();
    }

    public function testNonMember(): void
    {
        Yii::$app->user->setIdentity(new UserIdentity(['id' => '11']));

        $this->assertEmpty($this->podium()->account->getMembership());
        $this->assertEmpty($this->podium()->account->getId());
    }

    public function testMembership(): void
    {
        Yii::$app->user->setIdentity(new UserIdentity(['id' => '10']));

        $membership = $this->podium()->account->getMembership();
        $this->assertNotEmpty($membership);
        $this->assertEquals(10, $this->podium()->account->getId());
    }

    /**
     * @throws NoMembershipException
     */
    public function testFailedMembershipEnsure(): void
    {
        $this->expectException(NoMembershipException::class);
        (new Account())->ensureMembership();
    }
}
