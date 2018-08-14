<?php

declare(strict_types=1);

namespace bizley\podium\tests\base;

use bizley\podium\api\enums\MemberStatus;
use bizley\podium\api\models\forum\ForumRemover;
use bizley\podium\api\repos\ForumRepo;
use bizley\podium\api\repos\PostRepo;
use bizley\podium\api\repos\ThreadRepo;
use bizley\podium\tests\DbTestCase;
use yii\base\Event;

/**
 * Class ForumRemoverTest
 * @package bizley\podium\tests\base
 */
class ForumRemoverTest extends DbTestCase
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
                'created_at' => 1,
                'updated_at' => 1,
                'archived' => true,
            ],
            [
                'id' => 2,
                'category_id' => 1,
                'author_id' => 1,
                'name' => 'forum2',
                'slug' => 'forum2',
                'created_at' => 1,
                'updated_at' => 1,
                'archived' => false,
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
    protected static $eventsRaised = [];

    public function testRemove(): void
    {
        Event::on(ForumRemover::class, ForumRemover::EVENT_BEFORE_REMOVING, function () {
            static::$eventsRaised[ForumRemover::EVENT_BEFORE_REMOVING] = true;
        });
        Event::on(ForumRemover::class, ForumRemover::EVENT_AFTER_REMOVING, function () {
            static::$eventsRaised[ForumRemover::EVENT_AFTER_REMOVING] = true;
        });

        $this->assertTrue($this->podium()->forum->remove(ForumRemover::findOne(1)));

        $this->assertEmpty(ForumRepo::findOne(1));
        $this->assertEmpty(ThreadRepo::findOne(1));
        $this->assertEmpty(PostRepo::findOne(1));

        $this->assertArrayHasKey(ForumRemover::EVENT_BEFORE_REMOVING, static::$eventsRaised);
        $this->assertArrayHasKey(ForumRemover::EVENT_AFTER_REMOVING, static::$eventsRaised);
    }

    public function testRemoveEventPreventing(): void
    {
        $handler = function ($event) {
            $event->canRemove = false;
        };
        Event::on(ForumRemover::class, ForumRemover::EVENT_BEFORE_REMOVING, $handler);

        $this->assertFalse($this->podium()->forum->remove(ForumRemover::findOne(1)));

        $this->assertNotEmpty(ForumRepo::findOne(1));
        $this->assertNotEmpty(ThreadRepo::findOne(1));
        $this->assertNotEmpty(PostRepo::findOne(1));

        Event::off(ForumRemover::class, ForumRemover::EVENT_BEFORE_REMOVING, $handler);
    }

    public function testNonArchived(): void
    {
        $this->assertFalse($this->podium()->forum->remove(ForumRemover::findOne(2)));
        $this->assertNotEmpty(ForumRepo::findOne(2));
    }
}
