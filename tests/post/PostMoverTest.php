<?php

declare(strict_types=1);

namespace bizley\podium\tests\post;

use bizley\podium\api\base\ModelNotFoundException;
use bizley\podium\api\enums\MemberStatus;
use bizley\podium\api\models\category\Category;
use bizley\podium\api\models\forum\Forum;
use bizley\podium\api\models\post\PostMover;
use bizley\podium\api\models\thread\Thread;
use bizley\podium\api\repos\ForumRepo;
use bizley\podium\api\repos\PostRepo;
use bizley\podium\api\repos\ThreadRepo;
use bizley\podium\tests\DbTestCase;
use yii\base\Event;
use yii\base\NotSupportedException;

/**
 * Class PostMoverTest
 * @package bizley\podium\tests\post
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
                'threads_count' => 10,
                'posts_count' => 10,
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
                'posts_count' => 3,
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
                'posts_count' => 7,
                'created_at' => 1,
                'updated_at' => 1,
            ],
            [
                'id' => 3,
                'category_id' => 1,
                'forum_id' => 1,
                'author_id' => 1,
                'name' => 'thread3',
                'slug' => 'thread3',
                'posts_count' => 1,
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
                'thread_id' => 3,
                'author_id' => 1,
                'content' => 'post2',
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
    public function testMove(): void
    {
        Event::on(PostMover::class, PostMover::EVENT_BEFORE_MOVING, function () {
            $this->eventsRaised[PostMover::EVENT_BEFORE_MOVING] = true;
        });
        Event::on(PostMover::class, PostMover::EVENT_AFTER_MOVING, function () {
            $this->eventsRaised[PostMover::EVENT_AFTER_MOVING] = true;
        });

        $this->assertTrue($this->podium()->post->move(1, Thread::findOne(2))->result);

        $post = PostRepo::findOne(1);
        $this->assertEquals(1, $post->category_id);
        $this->assertEquals(1, $post->forum_id);
        $this->assertEquals(2, $post->thread_id);

        $this->assertEquals(2, ThreadRepo::findOne(1)->posts_count);
        $this->assertEquals(8, ThreadRepo::findOne(2)->posts_count);
        $this->assertEquals(10, ForumRepo::findOne(1)->posts_count);

        $this->assertArrayHasKey(PostMover::EVENT_BEFORE_MOVING, $this->eventsRaised);
        $this->assertArrayHasKey(PostMover::EVENT_AFTER_MOVING, $this->eventsRaised);
    }

    /**
     * @throws ModelNotFoundException
     */
    public function testMoveEventPreventing(): void
    {
        $handler = static function ($event) {
            $event->canMove = false;
        };
        Event::on(PostMover::class, PostMover::EVENT_BEFORE_MOVING, $handler);

        $this->assertFalse($this->podium()->post->move(1, Thread::findOne(2))->result);
        $this->assertEquals(1, PostRepo::findOne(1)->thread_id);

        Event::off(PostMover::class, PostMover::EVENT_BEFORE_MOVING, $handler);
    }

    /**
     * @throws ModelNotFoundException
     */
    public function testMoveLastOne(): void
    {
        $this->assertTrue($this->podium()->post->move(2, Thread::findOne(1))->result);

        $post = PostRepo::findOne(2);
        $this->assertEquals(1, $post->category_id);
        $this->assertEquals(1, $post->forum_id);
        $this->assertEquals(1, $post->thread_id);

        $this->assertEquals(4, ThreadRepo::findOne(1)->posts_count);
        $this->assertEmpty(ThreadRepo::findOne(3));

        $forum = ForumRepo::findOne(1);
        $this->assertEquals(10, $forum->posts_count);
        $this->assertEquals(9, $forum->threads_count);
    }

    public function testFailedMoveValidate(): void
    {
        $mock = $this->getMockBuilder(PostMover::class)->setMethods(['validate'])->getMock();
        $mock->method('validate')->willReturn(false);

        $this->assertFalse($mock->move()->result);
    }

    public function testFailedMove(): void
    {
        $mock = $this->getMockBuilder(PostMover::class)->setMethods(['save'])->getMock();
        $mock->method('save')->willReturn(false);

        $this->assertFalse($mock->move()->result);
    }

    /**
     * @throws NotSupportedException
     */
    public function testSetCategory(): void
    {
        $this->expectException(NotSupportedException::class);
        (new PostMover())->setCategory(Category::findOne(1));
    }

    /**
     * @throws NotSupportedException
     */
    public function testSetForum(): void
    {
        $this->expectException(NotSupportedException::class);
        (new PostMover())->setForum(Forum::findOne(1));
    }
}
