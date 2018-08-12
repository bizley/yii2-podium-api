<?php

declare(strict_types=1);

namespace bizley\podium\tests\base;

use bizley\podium\api\enums\MemberStatus;
use bizley\podium\api\models\post\PostRemover;
use bizley\podium\api\repos\PostRepo;
use bizley\podium\api\repos\ThreadRepo;
use bizley\podium\tests\DbTestCase;
use yii\base\Event;

/**
 * Class PostRemoverTest
 * @package bizley\podium\tests\base
 */
class PostRemoverTest extends DbTestCase
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
        ],
        'podium_forum' => [
            [
                'id' => 1,
                'category_id' => 1,
                'author_id' => 1,
                'name' => 'forum1',
                'slug' => 'forum1',
                'threads_count' => 8,
                'posts_count' => 39,
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
                'posts_count' => 16,
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
                'posts_count' => 0,
                'created_at' => 1,
                'updated_at' => 1,
                'archived' => true,
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
                'archived' => true,
            ],
            [
                'id' => 2,
                'category_id' => 1,
                'forum_id' => 1,
                'thread_id' => 1,
                'author_id' => 1,
                'content' => 'post2',
                'created_at' => 1,
                'updated_at' => 1,
                'archived' => false,
            ],
            [
                'id' => 3,
                'category_id' => 1,
                'forum_id' => 1,
                'thread_id' => 2,
                'author_id' => 1,
                'content' => 'post3',
                'created_at' => 1,
                'updated_at' => 1,
                'archived' => true,
            ],
        ],
    ];

    /**
     * @var array
     */
    protected static $eventsRaised = [];

    public function testRemove(): void
    {
        Event::on(PostRemover::class, PostRemover::EVENT_BEFORE_REMOVING, function () {
            static::$eventsRaised[PostRemover::EVENT_BEFORE_REMOVING] = true;
        });
        Event::on(PostRemover::class, PostRemover::EVENT_AFTER_REMOVING, function () {
            static::$eventsRaised[PostRemover::EVENT_AFTER_REMOVING] = true;
        });

        $this->assertTrue($this->podium()->post->remove(PostRemover::findOne(1)));

        $this->assertEmpty(PostRepo::findOne(1));

        $this->assertArrayHasKey(PostRemover::EVENT_BEFORE_REMOVING, static::$eventsRaised);
        $this->assertArrayHasKey(PostRemover::EVENT_AFTER_REMOVING, static::$eventsRaised);
    }

    public function testRemoveEventPreventing(): void
    {
        $handler = function ($event) {
            $event->canRemove = false;
        };
        Event::on(PostRemover::class, PostRemover::EVENT_BEFORE_REMOVING, $handler);

        $this->assertFalse($this->podium()->post->remove(PostRemover::findOne(1)));

        $this->assertNotEmpty(PostRepo::findOne(1));

        Event::off(PostRemover::class, PostRemover::EVENT_BEFORE_REMOVING, $handler);
    }

    public function testRemoveLastOne(): void
    {
        $this->assertTrue($this->podium()->post->remove(PostRemover::findOne(3)));

        $this->assertEmpty(PostRepo::findOne(3));
        $this->assertEmpty(ThreadRepo::findOne(2));
    }
}
