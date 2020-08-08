<?php

declare(strict_types=1);

namespace bizley\podium\tests\account;

use bizley\podium\api\components\NoMembershipException;
use bizley\podium\api\enums\AcquaintanceType;
use bizley\podium\api\enums\MemberStatus;
use bizley\podium\api\models\member\Member;
use bizley\podium\api\models\member\MemberBefriender;
use bizley\podium\api\repos\AcquaintanceActiveRecord;
use bizley\podium\tests\AccountTestCase;
use bizley\podium\tests\props\UserIdentity;
use Yii;
use yii\base\Event;
use yii\db\Exception;

/**
 * Class AccountBefrienderTest
 * @package bizley\podium\tests\account
 */
class AccountBefrienderTest extends AccountTestCase
{
    /**
     * @var array
     */
    public $fixtures = [
        'podium_member' => [
            [
                'id' => 10,
                'user_id' => '10',
                'username' => 'member1',
                'slug' => 'member1',
                'status_id' => MemberStatus::ACTIVE,
                'created_at' => 1,
                'updated_at' => 1,
            ],
            [
                'id' => 11,
                'user_id' => '11',
                'username' => 'member2',
                'slug' => 'member2',
                'status_id' => MemberStatus::ACTIVE,
                'created_at' => 1,
                'updated_at' => 1,
            ],
            [
                'id' => 12,
                'user_id' => '12',
                'username' => 'member3',
                'slug' => 'member3',
                'status_id' => MemberStatus::ACTIVE,
                'created_at' => 1,
                'updated_at' => 1,
            ],
        ],
        'podium_acquaintance' => [
            [
                'member_id' => 10,
                'target_id' => 12,
                'type_id' => AcquaintanceType::FRIEND,
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
        Yii::$app->user->setIdentity(new UserIdentity(['id' => '10']));
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
    public function testBefriend(): void
    {
        Event::on(MemberBefriender::class, MemberBefriender::EVENT_BEFORE_BEFRIENDING, function () {
            $this->eventsRaised[MemberBefriender::EVENT_BEFORE_BEFRIENDING] = true;
        });
        Event::on(MemberBefriender::class, MemberBefriender::EVENT_AFTER_BEFRIENDING, function () {
            $this->eventsRaised[MemberBefriender::EVENT_AFTER_BEFRIENDING] = true;
        });

        $this->assertTrue($this->podium()->account->befriendMember(Member::findOne(11))->result);

        $this->assertNotEmpty(AcquaintanceActiveRecord::findOne([
            'member_id' => 10,
            'target_id' => 11,
            'type_id' => AcquaintanceType::FRIEND,
        ]));

        $this->assertArrayHasKey(MemberBefriender::EVENT_BEFORE_BEFRIENDING, $this->eventsRaised);
        $this->assertArrayHasKey(MemberBefriender::EVENT_AFTER_BEFRIENDING, $this->eventsRaised);
    }

    /**
     * @throws NoMembershipException
     */
    public function testBefriendEventPreventing(): void
    {
        $handler = static function ($event) {
            $event->canBeFriends = false;
        };
        Event::on(MemberBefriender::class, MemberBefriender::EVENT_BEFORE_BEFRIENDING, $handler);

        $this->assertFalse($this->podium()->account->befriendMember(Member::findOne(11))->result);

        $this->assertEmpty(AcquaintanceActiveRecord::findOne([
            'member_id' => 10,
            'target_id' => 11,
            'type_id' => AcquaintanceType::FRIEND,
        ]));

        Event::off(MemberBefriender::class, MemberBefriender::EVENT_BEFORE_BEFRIENDING, $handler);
    }

    /**
     * @throws NoMembershipException
     */
    public function testBefriendAgain(): void
    {
        $this->assertFalse($this->podium()->account->befriendMember(Member::findOne(12))->result);
    }

    /**
     * @throws NoMembershipException
     */
    public function testUnfriend(): void
    {
        Event::on(MemberBefriender::class, MemberBefriender::EVENT_BEFORE_UNFRIENDING, function () {
            $this->eventsRaised[MemberBefriender::EVENT_BEFORE_UNFRIENDING] = true;
        });
        Event::on(MemberBefriender::class, MemberBefriender::EVENT_AFTER_UNFRIENDING, function () {
            $this->eventsRaised[MemberBefriender::EVENT_AFTER_UNFRIENDING] = true;
        });

        $this->assertTrue($this->podium()->account->unfriendMember(Member::findOne(12))->result);

        $this->assertEmpty(AcquaintanceActiveRecord::findOne([
            'member_id' => 10,
            'target_id' => 12,
            'type_id' => AcquaintanceType::FRIEND,
        ]));

        $this->assertArrayHasKey(MemberBefriender::EVENT_BEFORE_UNFRIENDING, $this->eventsRaised);
        $this->assertArrayHasKey(MemberBefriender::EVENT_AFTER_UNFRIENDING, $this->eventsRaised);
    }

    /**
     * @throws NoMembershipException
     */
    public function testUnfriendEventPreventing(): void
    {
        $handler = static function ($event) {
            $event->canUnfriend = false;
        };
        Event::on(MemberBefriender::class, MemberBefriender::EVENT_BEFORE_UNFRIENDING, $handler);

        $this->assertFalse($this->podium()->account->unfriendMember(Member::findOne(12))->result);

        $this->assertNotEmpty(AcquaintanceActiveRecord::findOne([
            'member_id' => 10,
            'target_id' => 12,
            'type_id' => AcquaintanceType::FRIEND,
        ]));

        Event::off(MemberBefriender::class, MemberBefriender::EVENT_BEFORE_UNFRIENDING, $handler);
    }

    /**
     * @throws NoMembershipException
     */
    public function testUnfriendAgain(): void
    {
        $this->assertFalse($this->podium()->account->unfriendMember(Member::findOne(11))->result);
    }
}
