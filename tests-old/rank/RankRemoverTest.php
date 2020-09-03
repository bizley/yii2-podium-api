<?php

declare(strict_types=1);

namespace bizley\podium\tests\rank;

use bizley\podium\api\components\ModelNotFoundException;
use bizley\podium\api\models\rank\RankRemover;
use bizley\podium\api\repos\RankRepo;
use bizley\podium\tests\DbTestCase;
use Exception;
use yii\base\Event;

/**
 * Class RankRemoverTest
 * @package bizley\podium\tests\rank
 */
class RankRemoverTest extends DbTestCase
{
    /**
     * @var array
     */
    public $fixtures = [
        'podium_rank' => [
            [
                'id' => 1,
                'name' => 'rank1',
                'min_posts' => 0,
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
        Event::on(RankRemover::class, RankRemover::EVENT_BEFORE_REMOVING, function () {
            $this->eventsRaised[RankRemover::EVENT_BEFORE_REMOVING] = true;
        });
        Event::on(RankRemover::class, RankRemover::EVENT_AFTER_REMOVING, function () {
            $this->eventsRaised[RankRemover::EVENT_AFTER_REMOVING] = true;
        });

        $this->assertTrue($this->podium()->rank->remove(1)->result);

        $this->assertEmpty(RankRepo::findOne(1));

        $this->assertArrayHasKey(RankRemover::EVENT_BEFORE_REMOVING, $this->eventsRaised);
        $this->assertArrayHasKey(RankRemover::EVENT_AFTER_REMOVING, $this->eventsRaised);
    }

    /**
     * @throws ModelNotFoundException
     */
    public function testRemoveEventPreventing(): void
    {
        $handler = static function ($event) {
            $event->canRemove = false;
        };
        Event::on(RankRemover::class, RankRemover::EVENT_BEFORE_REMOVING, $handler);

        $this->assertFalse($this->podium()->rank->remove(1)->result);

        $this->assertNotEmpty(RankRepo::findOne(1));

        Event::off(RankRemover::class, RankRemover::EVENT_BEFORE_REMOVING, $handler);
    }

    public function testExceptionRemove(): void
    {
        $mock = $this->getMockBuilder(RankRemover::class)->setMethods(['delete'])->getMock();
        $mock->method('delete')->will($this->throwException(new Exception()));

        $this->assertFalse($mock->remove()->result);
    }

    public function testFailedRemove(): void
    {
        $mock = $this->getMockBuilder(RankRemover::class)->setMethods(['delete'])->getMock();
        $mock->method('delete')->willReturn(false);

        $this->assertFalse($mock->remove()->result);
    }

    /**
     * @throws ModelNotFoundException
     */
    public function testNoRankToRemove(): void
    {
        $this->expectException(ModelNotFoundException::class);
        $this->podium()->rank->remove(999);
    }
}
