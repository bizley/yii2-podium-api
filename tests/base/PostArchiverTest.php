<?php

declare(strict_types=1);

namespace bizley\podium\tests\base;

use bizley\podium\api\enums\MemberStatus;
use bizley\podium\api\models\post\PostArchiver;
use bizley\podium\api\repos\ForumRepo;
use bizley\podium\api\repos\PostRepo;
use bizley\podium\api\repos\ThreadRepo;
use bizley\podium\tests\DbTestCase;
use yii\base\Event;

/**
 * Class PostArchiverTest
 * @package bizley\podium\tests\base
 */
class PostArchiverTest extends DbTestCase
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
                'archived' => false,
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
        Event::on(PostArchiver::class, PostArchiver::EVENT_BEFORE_ARCHIVING, function () {
            static::$eventsRaised[PostArchiver::EVENT_BEFORE_ARCHIVING] = true;
        });
        Event::on(PostArchiver::class, PostArchiver::EVENT_AFTER_ARCHIVING, function () {
            static::$eventsRaised[PostArchiver::EVENT_AFTER_ARCHIVING] = true;
        });

        $this->assertTrue($this->podium()->post->archive(PostArchiver::findOne(1)));

        $this->assertEquals(true, PostRepo::findOne(1)->archived);

        $this->assertEquals(20, ThreadRepo::findOne(1)->posts_count);
        $this->assertEquals(66, ForumRepo::findOne(1)->posts_count);

        $this->assertArrayHasKey(PostArchiver::EVENT_BEFORE_ARCHIVING, static::$eventsRaised);
        $this->assertArrayHasKey(PostArchiver::EVENT_AFTER_ARCHIVING, static::$eventsRaised);
    }

    public function testArchiveEventPreventing(): void
    {
        $handler = function ($event) {
            $event->canArchive = false;
        };
        Event::on(PostArchiver::class, PostArchiver::EVENT_BEFORE_ARCHIVING, $handler);

        $this->assertFalse($this->podium()->post->archive(PostArchiver::findOne(1)));

        $this->assertEquals(false, PostRepo::findOne(1)->archived);

        $this->assertEquals(21, ThreadRepo::findOne(1)->posts_count);
        $this->assertEquals(67, ForumRepo::findOne(1)->posts_count);

        Event::off(PostArchiver::class, PostArchiver::EVENT_BEFORE_ARCHIVING, $handler);
    }

    public function testAlreadyArchived(): void
    {
        $this->assertFalse($this->podium()->post->archive(PostArchiver::findOne(2)));
    }

    public function testRevive(): void
    {
        Event::on(PostArchiver::class, PostArchiver::EVENT_BEFORE_REVIVING, function () {
            static::$eventsRaised[PostArchiver::EVENT_BEFORE_REVIVING] = true;
        });
        Event::on(PostArchiver::class, PostArchiver::EVENT_AFTER_REVIVING, function () {
            static::$eventsRaised[PostArchiver::EVENT_AFTER_REVIVING] = true;
        });

        $this->assertTrue($this->podium()->post->revive(PostArchiver::findOne(2)));

        $this->assertEquals(false, PostRepo::findOne(2)->archived);

        $this->assertEquals(22, ThreadRepo::findOne(1)->posts_count);
        $this->assertEquals(68, ForumRepo::findOne(1)->posts_count);

        $this->assertArrayHasKey(PostArchiver::EVENT_BEFORE_REVIVING, static::$eventsRaised);
        $this->assertArrayHasKey(PostArchiver::EVENT_AFTER_REVIVING, static::$eventsRaised);
    }

    public function testReviveEventPreventing(): void
    {
        $handler = function ($event) {
            $event->canRevive = false;
        };
        Event::on(PostArchiver::class, PostArchiver::EVENT_BEFORE_REVIVING, $handler);

        $this->assertFalse($this->podium()->post->revive(PostArchiver::findOne(2)));

        $this->assertEquals(true, PostRepo::findOne(2)->archived);

        $this->assertEquals(21, ThreadRepo::findOne(1)->posts_count);
        $this->assertEquals(67, ForumRepo::findOne(1)->posts_count);

        Event::off(PostArchiver::class, PostArchiver::EVENT_BEFORE_REVIVING, $handler);
    }

    public function testAlreadyRevived(): void
    {
        $this->assertFalse($this->podium()->post->revive(PostArchiver::findOne(1)));
    }
}
