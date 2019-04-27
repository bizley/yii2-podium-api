<?php

declare(strict_types=1);

namespace bizley\podium\tests\member;

use bizley\podium\api\enums\AcquaintanceType;
use bizley\podium\api\enums\MemberStatus;
use bizley\podium\api\models\member\Member;
use bizley\podium\api\models\member\MemberIgnorer;
use bizley\podium\api\repos\AcquaintanceRepo;
use bizley\podium\tests\DbTestCase;
use yii\base\Event;

/**
 * Class MemberIgnoringTest
 * @package bizley\podium\tests\member
 */
class MemberIgnoringTest extends DbTestCase
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
                'type_id' => AcquaintanceType::IGNORE,
                'created_at' => 1,
            ],
        ],
    ];

    /**
     * @var array
     */
    protected $eventsRaised = [];

    public function testIgnore(): void
    {
        Event::on(MemberIgnorer::class, MemberIgnorer::EVENT_BEFORE_IGNORING, function () {
            $this->eventsRaised[MemberIgnorer::EVENT_BEFORE_IGNORING] = true;
        });
        Event::on(MemberIgnorer::class, MemberIgnorer::EVENT_AFTER_IGNORING, function () {
            $this->eventsRaised[MemberIgnorer::EVENT_AFTER_IGNORING] = true;
        });

        $this->assertTrue($this->podium()->member->ignore(Member::findOne(100), Member::findOne(101))->result);

        $this->assertNotEmpty(AcquaintanceRepo::findOne([
            'member_id' => 100,
            'target_id' => 101,
            'type_id' => AcquaintanceType::IGNORE,
        ]));

        $this->assertArrayHasKey(MemberIgnorer::EVENT_BEFORE_IGNORING, $this->eventsRaised);
        $this->assertArrayHasKey(MemberIgnorer::EVENT_AFTER_IGNORING, $this->eventsRaised);
    }

    public function testIgnoreEventPreventing(): void
    {
        $handler = static function ($event) {
            $event->canIgnore = false;
        };
        Event::on(MemberIgnorer::class, MemberIgnorer::EVENT_BEFORE_IGNORING, $handler);

        $this->assertFalse($this->podium()->member->ignore(Member::findOne(100), Member::findOne(101))->result);

        $this->assertEmpty(AcquaintanceRepo::findOne([
            'member_id' => 100,
            'target_id' => 101,
            'type_id' => AcquaintanceType::IGNORE,
        ]));

        Event::off(MemberIgnorer::class, MemberIgnorer::EVENT_BEFORE_IGNORING, $handler);
    }

    public function testIgnoreAgain(): void
    {
        $this->assertFalse($this->podium()->member->ignore(Member::findOne(101), Member::findOne(102))->result);
    }

    public function testFailedIgnore(): void
    {
        $mock = $this->getMockBuilder(MemberIgnorer::class)->setMethods(['save'])->getMock();
        $mock->method('save')->willReturn(false);

        $this->assertFalse($mock->ignore()->result);
    }

    public function testUnignore(): void
    {
        Event::on(MemberIgnorer::class, MemberIgnorer::EVENT_BEFORE_UNIGNORING, function () {
            $this->eventsRaised[MemberIgnorer::EVENT_BEFORE_UNIGNORING] = true;
        });
        Event::on(MemberIgnorer::class, MemberIgnorer::EVENT_AFTER_UNIGNORING, function () {
            $this->eventsRaised[MemberIgnorer::EVENT_AFTER_UNIGNORING] = true;
        });

        $this->assertTrue($this->podium()->member->unignore(Member::findOne(101), Member::findOne(102))->result);

        $this->assertEmpty(AcquaintanceRepo::findOne([
            'member_id' => 101,
            'target_id' => 102,
            'type_id' => AcquaintanceType::IGNORE,
        ]));

        $this->assertArrayHasKey(MemberIgnorer::EVENT_BEFORE_UNIGNORING, $this->eventsRaised);
        $this->assertArrayHasKey(MemberIgnorer::EVENT_AFTER_UNIGNORING, $this->eventsRaised);
    }

    public function testUnignoreEventPreventing(): void
    {
        $handler = static function ($event) {
            $event->canUnignore = false;
        };
        Event::on(MemberIgnorer::class, MemberIgnorer::EVENT_BEFORE_UNIGNORING, $handler);

        $this->assertFalse($this->podium()->member->unignore(Member::findOne(101), Member::findOne(102))->result);

        $this->assertNotEmpty(AcquaintanceRepo::findOne([
            'member_id' => 101,
            'target_id' => 102,
            'type_id' => AcquaintanceType::IGNORE,
        ]));

        Event::off(MemberIgnorer::class, MemberIgnorer::EVENT_BEFORE_UNIGNORING, $handler);
    }

    public function testUnignoreAgain(): void
    {
        $this->assertFalse($this->podium()->member->unignore(Member::findOne(100), Member::findOne(101))->result);
    }

    public function testExceptionUnignore(): void
    {
        $mock = $this->getMockBuilder(MemberIgnorer::class)->setMethods(['afterUnignore'])->getMock();
        $mock->method('afterUnignore')->will($this->throwException(new \Exception()));

        $mock->setMember(Member::findOne(101));
        $mock->setTarget(Member::findOne(102));

        $this->assertFalse($mock->unignore()->result);
    }
}
