<?php

declare(strict_types=1);

namespace bizley\podium\tests\thread;

use bizley\podium\api\enums\MemberStatus;
use bizley\podium\api\models\member\Member;
use bizley\podium\api\models\post\Post;
use bizley\podium\api\models\thread\Bookmarking;
use bizley\podium\api\repos\BookmarkRepo;
use bizley\podium\tests\DbTestCase;
use yii\base\Event;

/**
 * Class ThreadBookmarkingTest
 * @package bizley\podium\tests\thread
 */
class ThreadBookmarkingTest extends DbTestCase
{
    /**
     * @var array
     */
    public $fixtures = [
        'podium_member' => [
            [
                'id' => 1,
                'user_id' => '1',
                'username' => 'member1',
                'slug' => 'member1',
                'status_id' => MemberStatus::ACTIVE,
                'created_at' => 1,
                'updated_at' => 1,
            ],
            [
                'id' => 2,
                'user_id' => '2',
                'username' => 'member2',
                'slug' => 'member2',
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
            [
                'id' => 2,
                'category_id' => 1,
                'forum_id' => 1,
                'thread_id' => 1,
                'author_id' => 1,
                'content' => 'post2',
                'created_at' => 100,
                'updated_at' => 100,
            ],
        ],
        'podium_bookmark' => [
            [
                'member_id' => 2,
                'thread_id' => 1,
                'last_seen' => 10,
                'updated_at' => 1,
            ],
        ],
    ];

    /**
     * @var array
     */
    protected $eventsRaised = [];

    public function testMark(): void
    {
        Event::on(Bookmarking::class, Bookmarking::EVENT_BEFORE_MARKING, function () {
            $this->eventsRaised[Bookmarking::EVENT_BEFORE_MARKING] = true;
        });
        Event::on(Bookmarking::class, Bookmarking::EVENT_AFTER_MARKING, function () {
            $this->eventsRaised[Bookmarking::EVENT_AFTER_MARKING] = true;
        });

        $this->assertTrue($this->podium()->thread->mark(Member::findOne(1), Post::findOne(1))->result);

        $bookmark = BookmarkRepo::findOne([
            'member_id' => 1,
            'thread_id' => 1,
        ]);
        $this->assertNotEmpty($bookmark);
        $this->assertEquals(1, $bookmark->last_seen);

        $this->assertArrayHasKey(Bookmarking::EVENT_BEFORE_MARKING, $this->eventsRaised);
        $this->assertArrayHasKey(Bookmarking::EVENT_AFTER_MARKING, $this->eventsRaised);
    }

    public function testMarkEventPreventing(): void
    {
        $handler = static function ($event) {
            $event->canMark = false;
        };
        Event::on(Bookmarking::class, Bookmarking::EVENT_BEFORE_MARKING, $handler);

        $this->assertFalse($this->podium()->thread->mark(Member::findOne(1), Post::findOne(1))->result);

        $this->assertEmpty(BookmarkRepo::findOne([
            'member_id' => 1,
            'thread_id' => 1,
        ]));

        Event::off(Bookmarking::class, Bookmarking::EVENT_BEFORE_MARKING, $handler);
    }

    public function testUpdateMark(): void
    {
        $this->assertTrue($this->podium()->thread->mark(Member::findOne(2), Post::findOne(2))->result);

        $this->assertEquals(100, BookmarkRepo::findOne([
            'member_id' => 2,
            'thread_id' => 1,
        ])->last_seen);
    }

    public function testNoUpdateMark(): void
    {
        $this->assertTrue($this->podium()->thread->mark(Member::findOne(2), Post::findOne(1))->result);

        $this->assertEquals(10, BookmarkRepo::findOne([
            'member_id' => 2,
            'thread_id' => 1,
        ])->last_seen);
    }
}
