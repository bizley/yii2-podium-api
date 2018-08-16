<?php

declare(strict_types=1);

namespace bizley\podium\tests\post;

use bizley\podium\api\enums\MemberStatus;
use bizley\podium\api\models\member\Member;
use bizley\podium\api\models\post\Liking;
use bizley\podium\api\models\post\Post;
use bizley\podium\api\repos\PostRepo;
use bizley\podium\api\repos\ThumbRepo;
use bizley\podium\tests\DbTestCase;
use yii\base\Event;

/**
 * Class PostLikingTest
 * @package bizley\podium\tests\post
 */
class PostLikingTest extends DbTestCase
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
            [
                'id' => 3,
                'user_id' => '3',
                'username' => 'member3',
                'slug' => 'member3',
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
                'likes' => 15,
                'dislikes' => 15,
            ],
        ],
        'podium_thumb' => [
            [
                'member_id' => 2,
                'post_id' => 1,
                'thumb' => 1,
                'created_at' => 1,
                'updated_at' => 1,
            ],
            [
                'member_id' => 3,
                'post_id' => 1,
                'thumb' => -1,
                'created_at' => 1,
                'updated_at' => 1,
            ],
        ],
    ];

    /**
     * @var array
     */
    protected static $eventsRaised = [];

    public function testThumbUp(): void
    {
        Event::on(Liking::class, Liking::EVENT_BEFORE_THUMB_UP, function () {
            static::$eventsRaised[Liking::EVENT_BEFORE_THUMB_UP] = true;
        });
        Event::on(Liking::class, Liking::EVENT_AFTER_THUMB_UP, function () {
            static::$eventsRaised[Liking::EVENT_AFTER_THUMB_UP] = true;
        });

        $this->assertTrue($this->podium()->post->thumbUp(Member::findOne(1), Post::findOne(1)));

        $this->assertEquals(1, ThumbRepo::findOne([
            'member_id' => 1,
            'post_id' => 1,
        ])->thumb);

        $post = PostRepo::findOne(1);
        $this->assertEquals(16, $post->likes);
        $this->assertEquals(15, $post->dislikes);

        $this->assertArrayHasKey(Liking::EVENT_BEFORE_THUMB_UP, static::$eventsRaised);
        $this->assertArrayHasKey(Liking::EVENT_AFTER_THUMB_UP, static::$eventsRaised);
    }

    public function testThumbUpEventPreventing(): void
    {
        $handler = function ($event) {
            $event->canThumbUp = false;
        };
        Event::on(Liking::class, Liking::EVENT_BEFORE_THUMB_UP, $handler);

        $this->assertFalse($this->podium()->post->thumbUp(Member::findOne(1), Post::findOne(1)));

        $this->assertEmpty(ThumbRepo::findOne([
            'member_id' => 1,
            'post_id' => 1,
        ]));

        $post = PostRepo::findOne(1);
        $this->assertEquals(15, $post->likes);
        $this->assertEquals(15, $post->dislikes);

        Event::off(Liking::class, Liking::EVENT_BEFORE_THUMB_UP, $handler);
    }

    public function testAlreadyThumbedUp(): void
    {
        $this->assertFalse($this->podium()->post->thumbUp(Member::findOne(2), Post::findOne(1)));
    }

    public function testChangeToThumbUp(): void
    {
        $this->assertTrue($this->podium()->post->thumbUp(Member::findOne(3), Post::findOne(1)));

        $this->assertEquals(1, ThumbRepo::findOne([
            'member_id' => 3,
            'post_id' => 1,
        ])->thumb);

        $post = PostRepo::findOne(1);
        $this->assertEquals(16, $post->likes);
        $this->assertEquals(14, $post->dislikes);
    }

    public function testThumbDown(): void
    {
        Event::on(Liking::class, Liking::EVENT_BEFORE_THUMB_DOWN, function () {
            static::$eventsRaised[Liking::EVENT_BEFORE_THUMB_DOWN] = true;
        });
        Event::on(Liking::class, Liking::EVENT_AFTER_THUMB_DOWN, function () {
            static::$eventsRaised[Liking::EVENT_AFTER_THUMB_DOWN] = true;
        });

        $this->assertTrue($this->podium()->post->thumbDown(Member::findOne(1), Post::findOne(1)));

        $this->assertEquals(-1, ThumbRepo::findOne([
            'member_id' => 1,
            'post_id' => 1,
        ])->thumb);

        $post = PostRepo::findOne(1);
        $this->assertEquals(15, $post->likes);
        $this->assertEquals(16, $post->dislikes);

        $this->assertArrayHasKey(Liking::EVENT_BEFORE_THUMB_DOWN, static::$eventsRaised);
        $this->assertArrayHasKey(Liking::EVENT_AFTER_THUMB_DOWN, static::$eventsRaised);
    }

    public function testThumbDownEventPreventing(): void
    {
        $handler = function ($event) {
            $event->canThumbDown = false;
        };
        Event::on(Liking::class, Liking::EVENT_BEFORE_THUMB_DOWN, $handler);

        $this->assertFalse($this->podium()->post->thumbDown(Member::findOne(1), Post::findOne(1)));

        $this->assertEmpty(ThumbRepo::findOne([
            'member_id' => 1,
            'post_id' => 1,
        ]));

        $post = PostRepo::findOne(1);
        $this->assertEquals(15, $post->likes);
        $this->assertEquals(15, $post->dislikes);

        Event::off(Liking::class, Liking::EVENT_BEFORE_THUMB_DOWN, $handler);
    }

    public function testAlreadyThumbedDown(): void
    {
        $this->assertFalse($this->podium()->post->thumbDown(Member::findOne(3), Post::findOne(1)));
    }

    public function testChangeToThumbDown(): void
    {
        $this->assertTrue($this->podium()->post->thumbDown(Member::findOne(2), Post::findOne(1)));

        $this->assertEquals(-1, ThumbRepo::findOne([
            'member_id' => 3,
            'post_id' => 1,
        ])->thumb);

        $post = PostRepo::findOne(1);
        $this->assertEquals(14, $post->likes);
        $this->assertEquals(16, $post->dislikes);
    }

    public function testThumbResetFromUp(): void
    {
        Event::on(Liking::class, Liking::EVENT_BEFORE_THUMB_RESET, function () {
            static::$eventsRaised[Liking::EVENT_BEFORE_THUMB_RESET] = true;
        });
        Event::on(Liking::class, Liking::EVENT_AFTER_THUMB_RESET, function () {
            static::$eventsRaised[Liking::EVENT_AFTER_THUMB_RESET] = true;
        });

        $this->assertTrue($this->podium()->post->thumbReset(Member::findOne(2), Post::findOne(1)));

        $this->assertEmpty(ThumbRepo::findOne([
            'member_id' => 2,
            'post_id' => 1,
        ]));

        $post = PostRepo::findOne(1);
        $this->assertEquals(14, $post->likes);
        $this->assertEquals(15, $post->dislikes);

        $this->assertArrayHasKey(Liking::EVENT_BEFORE_THUMB_RESET, static::$eventsRaised);
        $this->assertArrayHasKey(Liking::EVENT_AFTER_THUMB_RESET, static::$eventsRaised);
    }

    public function testThumbResetEventPreventing(): void
    {
        $handler = function ($event) {
            $event->canThumbReset = false;
        };
        Event::on(Liking::class, Liking::EVENT_BEFORE_THUMB_RESET, $handler);

        $this->assertFalse($this->podium()->post->thumbReset(Member::findOne(2), Post::findOne(1)));

        $this->assertNotEmpty(ThumbRepo::findOne([
            'member_id' => 2,
            'post_id' => 1,
        ]));

        $post = PostRepo::findOne(1);
        $this->assertEquals(15, $post->likes);
        $this->assertEquals(15, $post->dislikes);

        Event::off(Liking::class, Liking::EVENT_BEFORE_THUMB_RESET, $handler);
    }

    public function testNoThumbToReset(): void
    {
        $this->assertFalse($this->podium()->post->thumbReset(Member::findOne(1), Post::findOne(1)));
    }

    public function testThumbResetFromDown(): void
    {
        $this->assertTrue($this->podium()->post->thumbReset(Member::findOne(3), Post::findOne(1)));

        $this->assertEmpty(ThumbRepo::findOne([
            'member_id' => 3,
            'post_id' => 1,
        ]));

        $post = PostRepo::findOne(1);
        $this->assertEquals(15, $post->likes);
        $this->assertEquals(14, $post->dislikes);
    }
}
