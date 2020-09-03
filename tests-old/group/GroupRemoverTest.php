<?php

declare(strict_types=1);

namespace bizley\podium\tests\group;

use bizley\podium\api\components\ModelNotFoundException;
use bizley\podium\api\models\group\GroupRemover;
use bizley\podium\api\repos\GroupRepo;
use bizley\podium\tests\DbTestCase;
use yii\base\Event;

/**
 * Class GroupRemoverTest
 * @package bizley\podium\tests\group
 */
class GroupRemoverTest extends DbTestCase
{
    /**
     * @var array
     */
    public $fixtures = [
        'podium_group' => [
            [
                'id' => 1,
                'name' => 'group1',
                'created_at' => 1,
                'updated_at' => 1,
            ],
        ],
    ];

    /**
     * @var array
     */
    protected $eventsRaised = [];

    /**
     * @throws ModelNotFoundException
     */
    public function testRemove(): void
    {
        Event::on(GroupRemover::class, GroupRemover::EVENT_BEFORE_REMOVING, function () {
            $this->eventsRaised[GroupRemover::EVENT_BEFORE_REMOVING] = true;
        });
        Event::on(GroupRemover::class, GroupRemover::EVENT_AFTER_REMOVING, function () {
            $this->eventsRaised[GroupRemover::EVENT_AFTER_REMOVING] = true;
        });

        $this->assertTrue($this->podium()->group->remove(1)->result);

        $this->assertEmpty(GroupRepo::findOne(1));

        $this->assertArrayHasKey(GroupRemover::EVENT_BEFORE_REMOVING, $this->eventsRaised);
        $this->assertArrayHasKey(GroupRemover::EVENT_AFTER_REMOVING, $this->eventsRaised);
    }

    /**
     * @throws ModelNotFoundException
     */
    public function testRemoveEventPreventing(): void
    {
        $handler = static function ($event) {
            $event->canRemove = false;
        };
        Event::on(GroupRemover::class, GroupRemover::EVENT_BEFORE_REMOVING, $handler);

        $this->assertFalse($this->podium()->group->remove(1)->result);

        $this->assertNotEmpty(GroupRepo::findOne(1));

        Event::off(GroupRemover::class, GroupRemover::EVENT_BEFORE_REMOVING, $handler);
    }

    public function testFailedDelete(): void
    {
        $mock = $this->getMockBuilder(GroupRemover::class)->setMethods(['delete'])->getMock();
        $mock->method('delete')->willReturn(false);

        $this->assertFalse($mock->remove()->result);
    }

    public function testExceptionDelete(): void
    {
        $mock = $this->getMockBuilder(GroupRemover::class)->setMethods(['delete'])->getMock();
        $mock->method('delete')->will($this->throwException(new \Exception()));

        $this->assertFalse($mock->remove()->result);
    }

    /**
     * @throws ModelNotFoundException
     */
    public function testNoGroupToRemove(): void
    {
        $this->expectException(ModelNotFoundException::class);
        $this->podium()->group->remove(999);
    }
}
