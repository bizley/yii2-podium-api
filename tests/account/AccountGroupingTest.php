<?php

declare(strict_types=1);

namespace bizley\podium\tests\account;

use bizley\podium\api\base\NoMembershipException;
use bizley\podium\api\enums\MemberStatus;
use bizley\podium\api\models\group\Group;
use bizley\podium\api\models\member\MemberGrouper;
use bizley\podium\api\repos\GroupMemberRepo;
use bizley\podium\tests\AccountTestCase;
use bizley\podium\tests\props\UserIdentity;
use Yii;
use yii\base\Event;
use yii\db\Exception;

/**
 * Class AccountGroupingTest
 * @package bizley\podium\tests\account
 */
class AccountGroupingTest extends AccountTestCase
{
    /**
     * @var array
     */
    public $fixtures = [
        'podium_member' => [
            [
                'id' => 1,
                'user_id' => '1',
                'username' => 'member1',
                'slug' => 'member1',
                'status_id' => MemberStatus::ACTIVE,
                'created_at' => 1,
                'updated_at' => 1,
            ],
        ],
        'podium_group' => [
            [
                'id' => 1,
                'name' => 'group1',
                'created_at' => 1,
                'updated_at' => 1,
            ],
            [
                'id' => 2,
                'name' => 'group2',
                'created_at' => 1,
                'updated_at' => 1,
            ],
        ],
        'podium_group_member' => [
            [
                'member_id' => 1,
                'group_id' => 2,
                'created_at' => 1,
            ],
        ],
    ];

    /**
     * @var array
     */
    protected $eventsRaised = [];

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->fixturesUp();
        Yii::$app->user->setIdentity(new UserIdentity(['id' => '1']));
    }

    /**
     * @throws Exception
     */
    protected function tearDown(): void
    {
        $this->fixturesDown();
        parent::tearDown();
    }

    /**
     * @throws NoMembershipException
     */
    public function testJoin(): void
    {
        Event::on(MemberGrouper::class, MemberGrouper::EVENT_BEFORE_JOINING, function () {
            $this->eventsRaised[MemberGrouper::EVENT_BEFORE_JOINING] = true;
        });
        Event::on(MemberGrouper::class, MemberGrouper::EVENT_AFTER_JOINING, function () {
            $this->eventsRaised[MemberGrouper::EVENT_AFTER_JOINING] = true;
        });

        $this->assertTrue($this->podium()->account->joinGroup(Group::findOne(1))->result);

        $this->assertNotEmpty(GroupMemberRepo::findOne([
            'member_id' => 1,
            'group_id' => 1,
        ]));

        $this->assertArrayHasKey(MemberGrouper::EVENT_BEFORE_JOINING, $this->eventsRaised);
        $this->assertArrayHasKey(MemberGrouper::EVENT_AFTER_JOINING, $this->eventsRaised);
    }

    /**
     * @throws NoMembershipException
     */
    public function testJoinEventPreventing(): void
    {
        $handler = static function ($event) {
            $event->canJoin = false;
        };
        Event::on(MemberGrouper::class, MemberGrouper::EVENT_BEFORE_JOINING, $handler);

        $this->assertFalse($this->podium()->account->joinGroup(Group::findOne(1))->result);

        $this->assertEmpty(GroupMemberRepo::findOne([
            'member_id' => 1,
            'group_id' => 1,
        ]));

        Event::off(MemberGrouper::class, MemberGrouper::EVENT_BEFORE_JOINING, $handler);
    }

    /**
     * @throws NoMembershipException
     */
    public function testJoinAgain(): void
    {
        $this->assertFalse($this->podium()->account->joinGroup(Group::findOne(2))->result);
    }

    /**
     * @throws NoMembershipException
     */
    public function testLeave(): void
    {
        Event::on(MemberGrouper::class, MemberGrouper::EVENT_BEFORE_LEAVING, function () {
            $this->eventsRaised[MemberGrouper::EVENT_BEFORE_LEAVING] = true;
        });
        Event::on(MemberGrouper::class, MemberGrouper::EVENT_AFTER_LEAVING, function () {
            $this->eventsRaised[MemberGrouper::EVENT_AFTER_LEAVING] = true;
        });

        $this->assertTrue($this->podium()->account->leaveGroup(Group::findOne(2))->result);

        $this->assertEmpty(GroupMemberRepo::findOne([
            'member_id' => 1,
            'group_id' => 2,
        ]));

        $this->assertArrayHasKey(MemberGrouper::EVENT_BEFORE_LEAVING, $this->eventsRaised);
        $this->assertArrayHasKey(MemberGrouper::EVENT_AFTER_LEAVING, $this->eventsRaised);
    }

    /**
     * @throws NoMembershipException
     */
    public function testLeaveEventPreventing(): void
    {
        $handler = static function ($event) {
            $event->canLeave = false;
        };
        Event::on(MemberGrouper::class, MemberGrouper::EVENT_BEFORE_LEAVING, $handler);

        $this->assertFalse($this->podium()->account->leaveGroup(Group::findOne(2))->result);

        $this->assertNotEmpty(GroupMemberRepo::findOne([
            'member_id' => 1,
            'group_id' => 2,
        ]));

        Event::off(MemberGrouper::class, MemberGrouper::EVENT_BEFORE_LEAVING, $handler);
    }

    /**
     * @throws NoMembershipException
     */
    public function testLeaveAgain(): void
    {
        $this->assertFalse($this->podium()->account->leaveGroup(Group::findOne(1))->result);
    }
}
