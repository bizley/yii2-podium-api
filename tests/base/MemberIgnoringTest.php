<?php

declare(strict_types=1);

namespace bizley\podium\tests\base;

use bizley\podium\api\enums\AcquaintanceType;
use bizley\podium\api\enums\MemberStatus;
use bizley\podium\api\models\Ignoring;
use bizley\podium\api\models\Member;
use bizley\podium\api\repos\AcquaintanceRepo;
use bizley\podium\tests\DbTestCase;
use yii\base\Event;

/**
 * Class MemberIgnoringTest
 * @package bizley\podium\tests\base
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
                'status_id' => MemberStatus::ACTIVE,
                'created_at' => 1,
                'updated_at' => 1,
            ],
            [
                'id' => 101,
                'user_id' => '101',
                'username' => 'member2',
                'status_id' => MemberStatus::ACTIVE,
                'created_at' => 1,
                'updated_at' => 1,
            ],
            [
                'id' => 102,
                'user_id' => '102',
                'username' => 'member3',
                'status_id' => MemberStatus::ACTIVE,
                'created_at' => 1,
                'updated_at' => 1,
            ],
        ],
        'podium_acquaintance' => [
            [
                'id' => 100,
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
    protected static $eventsRaised = [];

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        Event::on(Ignoring::class, Ignoring::EVENT_BEFORE_IGNORING, function () {
            static::$eventsRaised[Ignoring::EVENT_BEFORE_IGNORING] = true;
        });
        Event::on(Ignoring::class, Ignoring::EVENT_AFTER_IGNORING, function () {
            static::$eventsRaised[Ignoring::EVENT_AFTER_IGNORING] = true;
        });
        Event::on(Ignoring::class, Ignoring::EVENT_BEFORE_UNIGNORING, function () {
            static::$eventsRaised[Ignoring::EVENT_BEFORE_UNIGNORING] = true;
        });
        Event::on(Ignoring::class, Ignoring::EVENT_AFTER_UNIGNORING, function () {
            static::$eventsRaised[Ignoring::EVENT_AFTER_UNIGNORING] = true;
        });
    }

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

    public function testIgnore(): void
    {
        $this->assertTrue($this->podium()->member->ignore(Member::findOne(100), Member::findOne(101)));

        $acq = AcquaintanceRepo::findOne([
            'member_id' => 100,
            'target_id' => 101,
            'type_id' => AcquaintanceType::IGNORE,
        ]);
        $this->assertNotEmpty($acq);

        $this->assertArrayHasKey(Ignoring::EVENT_BEFORE_IGNORING, static::$eventsRaised);
        $this->assertArrayHasKey(Ignoring::EVENT_AFTER_IGNORING, static::$eventsRaised);
    }

    public function testIgnoreEventPreventing(): void
    {
        $handler = function ($event) {
            $event->canIgnore = false;
        };
        Event::on(Ignoring::class, Ignoring::EVENT_BEFORE_IGNORING, $handler);

        $this->assertFalse($this->podium()->member->ignore(Member::findOne(100), Member::findOne(101)));

        $acq = AcquaintanceRepo::findOne([
            'member_id' => 100,
            'target_id' => 101,
            'type_id' => AcquaintanceType::IGNORE,
        ]);
        $this->assertEmpty($acq);

        Event::off(Ignoring::class, Ignoring::EVENT_BEFORE_IGNORING, $handler);
    }

    public function testIgnoreAgain(): void
    {
        $this->assertFalse($this->podium()->member->ignore(Member::findOne(101), Member::findOne(102)));
    }

    public function testUnignore(): void
    {
        $this->assertTrue($this->podium()->member->unignore(Member::findOne(101), Member::findOne(102)));

        $acq = AcquaintanceRepo::findOne([
            'member_id' => 101,
            'target_id' => 102,
            'type_id' => AcquaintanceType::IGNORE,
        ]);
        $this->assertEmpty($acq);

        $this->assertArrayHasKey(Ignoring::EVENT_BEFORE_UNIGNORING, static::$eventsRaised);
        $this->assertArrayHasKey(Ignoring::EVENT_AFTER_UNIGNORING, static::$eventsRaised);
    }

    public function testUnignoreEventPreventing(): void
    {
        $handler = function ($event) {
            $event->canUnignore = false;
        };
        Event::on(Ignoring::class, Ignoring::EVENT_BEFORE_UNIGNORING, $handler);

        $this->assertFalse($this->podium()->member->unignore(Member::findOne(101), Member::findOne(102)));

        $acq = AcquaintanceRepo::findOne([
            'member_id' => 101,
            'target_id' => 102,
            'type_id' => AcquaintanceType::IGNORE,
        ]);
        $this->assertNotEmpty($acq);

        Event::off(Ignoring::class, Ignoring::EVENT_BEFORE_UNIGNORING, $handler);
    }

    public function testUnignoreAgain(): void
    {
        $this->assertFalse($this->podium()->member->unignore(Member::findOne(100), Member::findOne(101)));
    }
}
