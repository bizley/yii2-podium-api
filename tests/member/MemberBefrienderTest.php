<?php

declare(strict_types=1);

namespace bizley\podium\tests\member;

use bizley\podium\api\enums\AcquaintanceType;
use bizley\podium\api\enums\MemberStatus;
use bizley\podium\api\models\member\Member;
use bizley\podium\api\models\member\MemberBefriender;
use bizley\podium\api\repos\AcquaintanceActiveRecord;
use bizley\podium\tests\DbTestCase;
use yii\base\Event;

/**
 * Class MemberBefrienderTest
 * @package bizley\podium\tests\member
 */
class MemberBefrienderTest extends DbTestCase
{
    /**
     * @var array
     */
    public $fixtures = [
        'podium_member' => [
            [
                'id' => 100,
                'user_id' => '100',
                'username' => 'member1',
                'slug' => 'member1',
                'status_id' => MemberStatus::ACTIVE,
                'created_at' => 1,
                'updated_at' => 1,
            ],
            [
                'id' => 101,
                'user_id' => '101',
                'username' => 'member2',
                'slug' => 'member2',
                'status_id' => MemberStatus::ACTIVE,
                'created_at' => 1,
                'updated_at' => 1,
            ],
            [
                'id' => 102,
                'user_id' => '102',
                'username' => 'member3',
                'slug' => 'member3',
                'status_id' => MemberStatus::ACTIVE,
                'created_at' => 1,
                'updated_at' => 1,
            ],
        ],
        'podium_acquaintance' => [
            [
                'member_id' => 101,
                'target_id' => 102,
                'type_id' => AcquaintanceType::FRIEND,
                'created_at' => 1,
            ],
        ],
    ];

    /**
     * @var array
     */
    protected $eventsRaised = [];

    public function testBefriend(): void
    {
        Event::on(MemberBefriender::class, MemberBefriender::EVENT_BEFORE_BEFRIENDING, function () {
            $this->eventsRaised[MemberBefriender::EVENT_BEFORE_BEFRIENDING] = true;
        });
        Event::on(MemberBefriender::class, MemberBefriender::EVENT_AFTER_BEFRIENDING, function () {
            $this->eventsRaised[MemberBefriender::EVENT_AFTER_BEFRIENDING] = true;
        });

        $this->assertTrue($this->podium()->member->befriend(Member::findOne(100), Member::findOne(101))->result);

        $this->assertNotEmpty(AcquaintanceActiveRecord::findOne([
            'member_id' => 100,
            'target_id' => 101,
            'type_id' => AcquaintanceType::FRIEND,
        ]));

        $this->assertArrayHasKey(MemberBefriender::EVENT_BEFORE_BEFRIENDING, $this->eventsRaised);
        $this->assertArrayHasKey(MemberBefriender::EVENT_AFTER_BEFRIENDING, $this->eventsRaised);
    }

    public function testBefriendEventPreventing(): void
    {
        $handler = static function ($event) {
            $event->canBeFriends = false;
        };
        Event::on(MemberBefriender::class, MemberBefriender::EVENT_BEFORE_BEFRIENDING, $handler);

        $this->assertFalse($this->podium()->member->befriend(Member::findOne(100), Member::findOne(101))->result);

        $this->assertEmpty(AcquaintanceActiveRecord::findOne([
            'member_id' => 100,
            'target_id' => 101,
            'type_id' => AcquaintanceType::FRIEND,
        ]));

        Event::off(MemberBefriender::class, MemberBefriender::EVENT_BEFORE_BEFRIENDING, $handler);
    }

    public function testBefriendAgain(): void
    {
        $this->assertFalse($this->podium()->member->befriend(Member::findOne(101), Member::findOne(102))->result);
    }

    public function testFailedBefriend(): void
    {
        $mock = $this->getMockBuilder(MemberBefriender::class)->setMethods(['save'])->getMock();
        $mock->method('save')->willReturn(false);

        $this->assertFalse($mock->befriend()->result);
    }

    public function testUnfriend(): void
    {
        Event::on(MemberBefriender::class, MemberBefriender::EVENT_BEFORE_UNFRIENDING, function () {
            $this->eventsRaised[MemberBefriender::EVENT_BEFORE_UNFRIENDING] = true;
        });
        Event::on(MemberBefriender::class, MemberBefriender::EVENT_AFTER_UNFRIENDING, function () {
            $this->eventsRaised[MemberBefriender::EVENT_AFTER_UNFRIENDING] = true;
        });

        $this->assertTrue($this->podium()->member->unfriend(Member::findOne(101), Member::findOne(102))->result);

        $this->assertEmpty(AcquaintanceActiveRecord::findOne([
            'member_id' => 101,
            'target_id' => 102,
            'type_id' => AcquaintanceType::FRIEND,
        ]));

        $this->assertArrayHasKey(MemberBefriender::EVENT_BEFORE_UNFRIENDING, $this->eventsRaised);
        $this->assertArrayHasKey(MemberBefriender::EVENT_AFTER_UNFRIENDING, $this->eventsRaised);
    }

    public function testUnfriendEventPreventing(): void
    {
        $handler = static function ($event) {
            $event->canUnfriend = false;
        };
        Event::on(MemberBefriender::class, MemberBefriender::EVENT_BEFORE_UNFRIENDING, $handler);

        $this->assertFalse($this->podium()->member->unfriend(Member::findOne(101), Member::findOne(102))->result);

        $this->assertNotEmpty(AcquaintanceActiveRecord::findOne([
            'member_id' => 101,
            'target_id' => 102,
            'type_id' => AcquaintanceType::FRIEND,
        ]));

        Event::off(MemberBefriender::class, MemberBefriender::EVENT_BEFORE_UNFRIENDING, $handler);
    }

    public function testUnfriendAgain(): void
    {
        $this->assertFalse($this->podium()->member->unfriend(Member::findOne(100), Member::findOne(101))->result);
    }

    public function testExceptionUnfriend(): void
    {
        $mock = $this->getMockBuilder(MemberBefriender::class)->setMethods(['afterUnfriend'])->getMock();
        $mock->method('afterUnfriend')->will($this->throwException(new \Exception()));

        $mock->setMember(Member::findOne(101));
        $mock->setTarget(Member::findOne(102));

        $this->assertFalse($mock->unfriend()->result);
    }
}
