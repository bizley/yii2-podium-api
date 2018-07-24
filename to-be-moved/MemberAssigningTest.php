<?php

declare(strict_types=1);

namespace bizley\podium\tests\base;

use bizley\podium\api\enums\MemberStatus;
use bizley\podium\api\enums\Role;
use bizley\podium\api\models\Member;
use bizley\podium\api\rbac\Assigning;
use bizley\podium\tests\DbTestCase;
use yii\base\Event;
use yii\db\Exception;

/**
 * Class MemberAssigningTest
 * @package bizley\podium\tests\base
 */
class MemberAssigningTest extends DbTestCase
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
        ],
    ];

    /**
     * @var array
     */
    protected static $eventsRaised = [];

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        Event::on(Assigning::class, Assigning::EVENT_BEFORE_SWITCH, function () {
            static::$eventsRaised[Assigning::EVENT_BEFORE_SWITCH] = true;
        });
        Event::on(Assigning::class, Assigning::EVENT_AFTER_SWITCH, function () {
            static::$eventsRaised[Assigning::EVENT_AFTER_SWITCH] = true;
        });

        \Yii::$app->podium->access->setDefault();
    }

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
        $this->podium()->access->revokeAll(100);
        $this->fixturesDown();
    }

    public function testFirstAssign(): void
    {
        $this->assertTrue($this->podium()->member->assign(Member::findOne(100), $this->podium()->access->getRole(Role::MEMBER)));
        $this->assertNotEmpty($this->podium()->access->getAssignment(Role::MEMBER, 100));

        $this->assertArrayHasKey(Assigning::EVENT_BEFORE_SWITCH, static::$eventsRaised);
        $this->assertArrayHasKey(Assigning::EVENT_AFTER_SWITCH, static::$eventsRaised);
    }

    public function testSwitch(): void
    {
        $this->podium()->member->assign(Member::findOne(100), $this->podium()->access->getRole(Role::MEMBER));
        $this->assertTrue($this->podium()->member->assign(Member::findOne(100), $this->podium()->access->getRole(Role::MODERATOR)));
        $this->assertEmpty($this->podium()->access->getAssignment(Role::MEMBER, 100));
        $this->assertNotEmpty($this->podium()->access->getAssignment(Role::MODERATOR, 100));
    }
}
