<?php

declare(strict_types=1);

namespace bizley\podium\tests\account;

use bizley\podium\api\enums\MemberStatus;
use bizley\podium\api\models\group\Group;
use bizley\podium\api\models\member\Grouping;
use bizley\podium\api\repos\GroupMemberRepo;
use bizley\podium\tests\AccountTestCase;
use bizley\podium\tests\props\UserIdentity;
use yii\base\Event;

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
    protected static $eventsRaised = [];

    /**
     * @throws \yii\db\Exception
     */
    protected function setUp(): void
    {
        $this->fixturesUp();
        \Yii::$app->user->setIdentity(new UserIdentity(['id' => '1']));
    }

    /**
     * @throws \yii\db\Exception
     */
    protected function tearDown(): void
    {
        $this->fixturesDown();
        parent::tearDown();
    }

    public function testJoin(): void
    {
        Event::on(Grouping::class, Grouping::EVENT_BEFORE_JOINING, function () {
            static::$eventsRaised[Grouping::EVENT_BEFORE_JOINING] = true;
        });
        Event::on(Grouping::class, Grouping::EVENT_AFTER_JOINING, function () {
            static::$eventsRaised[Grouping::EVENT_AFTER_JOINING] = true;
        });

        $this->assertTrue($this->podium()->account->join(Group::findOne(1))->result);

        $this->assertNotEmpty(GroupMemberRepo::findOne([
            'member_id' => 1,
            'group_id' => 1,
        ]));

        $this->assertArrayHasKey(Grouping::EVENT_BEFORE_JOINING, static::$eventsRaised);
        $this->assertArrayHasKey(Grouping::EVENT_AFTER_JOINING, static::$eventsRaised);
    }

    public function testJoinEventPreventing(): void
    {
        $handler = function ($event) {
            $event->canJoin = false;
        };
        Event::on(Grouping::class, Grouping::EVENT_BEFORE_JOINING, $handler);

        $this->assertFalse($this->podium()->account->join(Group::findOne(1))->result);

        $this->assertEmpty(GroupMemberRepo::findOne([
            'member_id' => 1,
            'group_id' => 1,
        ]));

        Event::off(Grouping::class, Grouping::EVENT_BEFORE_JOINING, $handler);
    }

    public function testJoinAgain(): void
    {
        $this->assertFalse($this->podium()->account->join(Group::findOne(2))->result);
    }

    public function testLeave(): void
    {
        Event::on(Grouping::class, Grouping::EVENT_BEFORE_LEAVING, function () {
            static::$eventsRaised[Grouping::EVENT_BEFORE_LEAVING] = true;
        });
        Event::on(Grouping::class, Grouping::EVENT_AFTER_LEAVING, function () {
            static::$eventsRaised[Grouping::EVENT_AFTER_LEAVING] = true;
        });

        $this->assertTrue($this->podium()->account->leave(Group::findOne(2))->result);

        $this->assertEmpty(GroupMemberRepo::findOne([
            'member_id' => 1,
            'group_id' => 2,
        ]));

        $this->assertArrayHasKey(Grouping::EVENT_BEFORE_LEAVING, static::$eventsRaised);
        $this->assertArrayHasKey(Grouping::EVENT_AFTER_LEAVING, static::$eventsRaised);
    }

    public function testLeaveEventPreventing(): void
    {
        $handler = function ($event) {
            $event->canLeave = false;
        };
        Event::on(Grouping::class, Grouping::EVENT_BEFORE_LEAVING, $handler);

        $this->assertFalse($this->podium()->account->leave(Group::findOne(2))->result);

        $this->assertNotEmpty(GroupMemberRepo::findOne([
            'member_id' => 1,
            'group_id' => 2,
        ]));

        Event::off(Grouping::class, Grouping::EVENT_BEFORE_LEAVING, $handler);
    }

    public function testLeaveAgain(): void
    {
        $this->assertFalse($this->podium()->account->leave(Group::findOne(1))->result);
    }
}
