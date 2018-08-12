<?php

declare(strict_types=1);

namespace bizley\podium\tests\base;

use bizley\podium\api\enums\MemberStatus;
use bizley\podium\api\models\thread\ThreadArchiver;
use bizley\podium\api\repos\ForumRepo;
use bizley\podium\api\repos\ThreadRepo;
use bizley\podium\tests\DbTestCase;
use yii\base\Event;

/**
 * Class ThreadArchiverTest
 * @package bizley\podium\tests\base
 */
class ThreadArchiverTest extends DbTestCase
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
                'threads_count' => 5,
                'posts_count' => 67,
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
                'posts_count' => 21,
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
                'posts_count' => 4,
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

    public function testArchive(): void
    {
        Event::on(ThreadArchiver::class, ThreadArchiver::EVENT_BEFORE_ARCHIVING, function () {
            static::$eventsRaised[ThreadArchiver::EVENT_BEFORE_ARCHIVING] = true;
        });
        Event::on(ThreadArchiver::class, ThreadArchiver::EVENT_AFTER_ARCHIVING, function () {
            static::$eventsRaised[ThreadArchiver::EVENT_AFTER_ARCHIVING] = true;
        });

        $this->assertTrue($this->podium()->thread->archive(ThreadArchiver::findOne(1)));

        $this->assertEquals(true, ThreadRepo::findOne(1)->archived);

        $forum = ForumRepo::findOne(1);
        $this->assertEquals(4, $forum->threads_count);
        $this->assertEquals(46, $forum->posts_count);

        $this->assertArrayHasKey(ThreadArchiver::EVENT_BEFORE_ARCHIVING, static::$eventsRaised);
        $this->assertArrayHasKey(ThreadArchiver::EVENT_AFTER_ARCHIVING, static::$eventsRaised);
    }

    public function testArchiveEventPreventing(): void
    {
        $handler = function ($event) {
            $event->canArchive = false;
        };
        Event::on(ThreadArchiver::class, ThreadArchiver::EVENT_BEFORE_ARCHIVING, $handler);

        $this->assertFalse($this->podium()->thread->archive(ThreadArchiver::findOne(1)));

        $this->assertEquals(false, ThreadRepo::findOne(1)->archived);

        $forum = ForumRepo::findOne(1);
        $this->assertEquals(5, $forum->threads_count);
        $this->assertEquals(67, $forum->posts_count);

        Event::off(ThreadArchiver::class, ThreadArchiver::EVENT_BEFORE_ARCHIVING, $handler);
    }

    public function testAlreadyArchived(): void
    {
        $this->assertFalse($this->podium()->thread->archive(ThreadArchiver::findOne(2)));
    }

    public function testRevive(): void
    {
        Event::on(ThreadArchiver::class, ThreadArchiver::EVENT_BEFORE_REVIVING, function () {
            static::$eventsRaised[ThreadArchiver::EVENT_BEFORE_REVIVING] = true;
        });
        Event::on(ThreadArchiver::class, ThreadArchiver::EVENT_AFTER_REVIVING, function () {
            static::$eventsRaised[ThreadArchiver::EVENT_AFTER_REVIVING] = true;
        });

        $this->assertTrue($this->podium()->thread->revive(ThreadArchiver::findOne(2)));

        $this->assertEquals(false, ThreadRepo::findOne(2)->archived);

        $forum = ForumRepo::findOne(1);
        $this->assertEquals(6, $forum->threads_count);
        $this->assertEquals(71, $forum->posts_count);

        $this->assertArrayHasKey(ThreadArchiver::EVENT_BEFORE_REVIVING, static::$eventsRaised);
        $this->assertArrayHasKey(ThreadArchiver::EVENT_AFTER_REVIVING, static::$eventsRaised);
    }

    public function testReviveEventPreventing(): void
    {
        $handler = function ($event) {
            $event->canRevive = false;
        };
        Event::on(ThreadArchiver::class, ThreadArchiver::EVENT_BEFORE_REVIVING, $handler);

        $this->assertFalse($this->podium()->thread->revive(ThreadArchiver::findOne(2)));

        $this->assertEquals(true, ThreadRepo::findOne(2)->archived);

        $forum = ForumRepo::findOne(1);
        $this->assertEquals(5, $forum->threads_count);
        $this->assertEquals(67, $forum->posts_count);

        Event::off(ThreadArchiver::class, ThreadArchiver::EVENT_BEFORE_REVIVING, $handler);
    }

    public function testAlreadyRevived(): void
    {
        $this->assertFalse($this->podium()->thread->revive(ThreadArchiver::findOne(1)));
    }
}
