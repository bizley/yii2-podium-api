<?php

declare(strict_types=1);

namespace bizley\podium\tests\forum;

use bizley\podium\api\base\ModelNotFoundException;
use bizley\podium\api\enums\MemberStatus;
use bizley\podium\api\models\category\Category;
use bizley\podium\api\models\forum\Forum;
use bizley\podium\api\models\forum\ForumMover;
use bizley\podium\api\models\thread\Thread;
use bizley\podium\api\repos\ForumRepo;
use bizley\podium\tests\DbTestCase;
use yii\base\Event;
use yii\base\NotSupportedException;

/**
 * Class ForumMoverTest
 * @package bizley\podium\tests\forum
 */
class ForumMoverTest extends DbTestCase
{
    /**
     * @var array
     */
    public $fixtures = [
        'podium_member' => [
            [
                'id' => 1,
                'user_id' => '1',
                'username' => 'member',
                'slug' => 'member',
                'status_id' => MemberStatus::ACTIVE,
                'created_at' => 1,
                'updated_at' => 1,
            ],
        ],
        'podium_category' => [
            [
                'id' => 1,
                'author_id' => 1,
                'name' => 'category1',
                'slug' => 'category1',
                'created_at' => 1,
                'updated_at' => 1,
            ],
            [
                'id' => 2,
                'author_id' => 1,
                'name' => 'category2',
                'slug' => 'category2',
                'created_at' => 1,
                'updated_at' => 1,
            ],
        ],
        'podium_forum' => [
            [
                'id' => 1,
                'category_id' => 1,
                'author_id' => 1,
                'name' => 'forum1',
                'slug' => 'forum1',
                'created_at' => 1,
                'updated_at' => 1,
            ]
        ],
    ];

    /**
     * @var array
     */
    protected $eventsRaised = [];

    /**
     * @throws ModelNotFoundException
     */
    public function testMove(): void
    {
        Event::on(ForumMover::class, ForumMover::EVENT_BEFORE_MOVING, function () {
            $this->eventsRaised[ForumMover::EVENT_BEFORE_MOVING] = true;
        });
        Event::on(ForumMover::class, ForumMover::EVENT_AFTER_MOVING, function () {
            $this->eventsRaised[ForumMover::EVENT_AFTER_MOVING] = true;
        });

        $this->assertTrue($this->podium()->forum->move(1, Category::findOne(2))->result);
        $this->assertEquals(2, ForumRepo::findOne(1)->category_id);

        $this->assertArrayHasKey(ForumMover::EVENT_BEFORE_MOVING, $this->eventsRaised);
        $this->assertArrayHasKey(ForumMover::EVENT_AFTER_MOVING, $this->eventsRaised);
    }

    /**
     * @throws ModelNotFoundException
     */
    public function testMoveEventPreventing(): void
    {
        $handler = static function ($event) {
            $event->canMove = false;
        };
        Event::on(ForumMover::class, ForumMover::EVENT_BEFORE_MOVING, $handler);

        $this->assertFalse($this->podium()->forum->move(1, Category::findOne(2))->result);
        $this->assertEquals(1, ForumRepo::findOne(1)->category_id);

        Event::off(ForumMover::class, ForumMover::EVENT_BEFORE_MOVING, $handler);
    }

    /**
     * @throws NotSupportedException
     */
    public function testSetForum(): void
    {
        $this->expectException(NotSupportedException::class);
        (new ForumMover())->prepareForum(new Forum());
    }

    /**
     * @throws NotSupportedException
     */
    public function testSetThread(): void
    {
        $this->expectException(NotSupportedException::class);
        (new ForumMover())->prepareThread(new Thread());
    }
}
