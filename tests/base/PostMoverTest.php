<?php

declare(strict_types=1);

namespace bizley\podium\tests\base;

use bizley\podium\api\enums\MemberStatus;
use bizley\podium\api\models\post\PostMover;
use bizley\podium\api\models\thread\Thread;
use bizley\podium\api\repos\PostRepo;
use bizley\podium\tests\DbTestCase;
use yii\base\Event;

/**
 * Class PostMoverTest
 * @package bizley\podium\tests\base
 */
class PostMoverTest extends DbTestCase
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
            ],
        ],
        'podium_thread' => [
            [
                'id' => 1,
                'category_id' => 1,
                'forum_id' => 1,
                'author_id' => 1,
                'name' => 'thread1',
                'slug' => 'thread1',
                'created_at' => 1,
                'updated_at' => 1,
            ],
            [
                'id' => 2,
                'category_id' => 1,
                'forum_id' => 1,
                'author_id' => 1,
                'name' => 'thread2',
                'slug' => 'thread2',
                'created_at' => 1,
                'updated_at' => 1,
            ],
        ],
        'podium_post' => [
            [
                'id' => 1,
                'category_id' => 1,
                'forum_id' => 1,
                'thread_id' => 1,
                'author_id' => 1,
                'content' => 'post1',
                'created_at' => 1,
                'updated_at' => 1,
            ],
        ],
    ];

    /**
     * @var array
     */
    protected $eventsRaised = [];

    public function testMove(): void
    {
        Event::on(PostMover::class, PostMover::EVENT_BEFORE_MOVING, function () {
            $this->eventsRaised[PostMover::EVENT_BEFORE_MOVING] = true;
        });
        Event::on(PostMover::class, PostMover::EVENT_AFTER_MOVING, function () {
            $this->eventsRaised[PostMover::EVENT_AFTER_MOVING] = true;
        });

        $this->assertTrue($this->podium()->post->move(PostMover::findOne(1), Thread::findOne(2)));
        $post = PostRepo::findOne(1);
        $this->assertEquals(1, $post->category_id);
        $this->assertEquals(1, $post->forum_id);
        $this->assertEquals(2, $post->thread_id);

        $this->assertArrayHasKey(PostMover::EVENT_BEFORE_MOVING, $this->eventsRaised);
        $this->assertArrayHasKey(PostMover::EVENT_AFTER_MOVING, $this->eventsRaised);
    }

    public function testMoveEventPreventing(): void
    {
        $handler = function ($event) {
            $event->canMove = false;
        };
        Event::on(PostMover::class, PostMover::EVENT_BEFORE_MOVING, $handler);

        $this->assertFalse($this->podium()->post->move(PostMover::findOne(1), Thread::findOne(2)));
        $this->assertEquals(1, PostRepo::findOne(1)->thread_id);

        Event::off(PostMover::class, PostMover::EVENT_BEFORE_MOVING, $handler);
    }
}
