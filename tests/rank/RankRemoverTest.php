<?php

declare(strict_types=1);

namespace bizley\podium\tests\rank;

use bizley\podium\api\models\rank\RankRemover;
use bizley\podium\api\repos\RankRepo;
use bizley\podium\tests\DbTestCase;
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
    protected static $eventsRaised = [];

    public function testRemove(): void
    {
        Event::on(RankRemover::class, RankRemover::EVENT_BEFORE_REMOVING, function () {
            static::$eventsRaised[RankRemover::EVENT_BEFORE_REMOVING] = true;
        });
        Event::on(RankRemover::class, RankRemover::EVENT_AFTER_REMOVING, function () {
            static::$eventsRaised[RankRemover::EVENT_AFTER_REMOVING] = true;
        });

        $this->assertTrue($this->podium()->rank->remove(RankRemover::findOne(1)));

        $this->assertEmpty(RankRepo::findOne(1));

        $this->assertArrayHasKey(RankRemover::EVENT_BEFORE_REMOVING, static::$eventsRaised);
        $this->assertArrayHasKey(RankRemover::EVENT_AFTER_REMOVING, static::$eventsRaised);
    }

    public function testRemoveEventPreventing(): void
    {
        $handler = function ($event) {
            $event->canRemove = false;
        };
        Event::on(RankRemover::class, RankRemover::EVENT_BEFORE_REMOVING, $handler);

        $this->assertFalse($this->podium()->rank->remove(RankRemover::findOne(1)));

        $this->assertNotEmpty(RankRepo::findOne(1));

        Event::off(RankRemover::class, RankRemover::EVENT_BEFORE_REMOVING, $handler);
    }
}
