<?php

declare(strict_types=1);

namespace bizley\podium\tests\post;

use bizley\podium\api\base\InsufficientDataException;
use bizley\podium\api\base\ModelNotFoundException;
use bizley\podium\api\enums\MemberStatus;
use bizley\podium\api\enums\PostType;
use bizley\podium\api\models\category\Category;
use bizley\podium\api\models\forum\Forum;
use bizley\podium\api\models\member\Member;
use bizley\podium\api\models\post\PostForm;
use bizley\podium\api\models\thread\Thread;
use bizley\podium\api\repos\ForumRepo;
use bizley\podium\api\repos\PostRepo;
use bizley\podium\api\repos\ThreadRepo;
use bizley\podium\tests\DbTestCase;
use yii\base\Event;
use yii\base\NotSupportedException;

/**
 * Class PostFormTest
 * @package bizley\podium\tests\post
 */
class PostFormTest extends DbTestCase
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
                'posts_count' => 3,
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
                'posts_count' => 2,
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

    public function testCreate(): void
    {
        Event::on(PostForm::class, PostForm::EVENT_BEFORE_CREATING, function () {
            $this->eventsRaised[PostForm::EVENT_BEFORE_CREATING] = true;
        });
        Event::on(PostForm::class, PostForm::EVENT_AFTER_CREATING, function () {
            $this->eventsRaised[PostForm::EVENT_AFTER_CREATING] = true;
        });

        $data = ['content' => 'post-new'];

        $response = $this->podium()->post->create($data, Member::findOne(1), Thread::findOne(1));
        $time = time();

        $this->assertTrue($response->result);
        $this->assertEquals([
            'id' => 2,
            'category_id' => 1,
            'forum_id' => 1,
            'thread_id' => 1,
            'author_id' => 1,
            'content' => 'post-new',
            'type_id' => 'post',
            'created_at' => $time,
            'updated_at' => $time,
        ], $response->data);

        $post = PostRepo::findOne(['content' => 'post-new']);
        $this->assertEquals(array_merge($data, [
            'author_id' => 1,
            'category_id' => 1,
            'forum_id' => 1,
            'thread_id' => 1,
            'edited' => false,
            'likes' => 0,
            'dislikes' => 0,
            'edited_at' => null,
            'type_id' => PostType::POST,
        ]), [
            'content' => $post->content,
            'author_id' => $post->author_id,
            'category_id' => $post->category_id,
            'forum_id' => $post->forum_id,
            'thread_id' => $post->thread_id,
            'edited' => $post->edited,
            'likes' => $post->likes,
            'dislikes' => $post->dislikes,
            'edited_at' => $post->edited_at,
            'type_id' => $post->type_id,
        ]);

        $this->assertEquals(3, ThreadRepo::findOne(1)->posts_count);
        $this->assertEquals(4, ForumRepo::findOne(1)->posts_count);

        $this->assertArrayHasKey(PostForm::EVENT_BEFORE_CREATING, $this->eventsRaised);
        $this->assertArrayHasKey(PostForm::EVENT_AFTER_CREATING, $this->eventsRaised);
    }

    public function testCreateEventPreventing(): void
    {
        $handler = function ($event) {
            $event->canCreate = false;
        };
        Event::on(PostForm::class, PostForm::EVENT_BEFORE_CREATING, $handler);

        $data = ['content' => 'post-new'];
        $this->assertFalse($this->podium()->post->create($data, Member::findOne(1), Thread::findOne(1))->result);

        $this->assertEmpty(PostRepo::findOne(['content' => 'post-new']));

        $this->assertEquals(2, ThreadRepo::findOne(1)->posts_count);
        $this->assertEquals(3, ForumRepo::findOne(1)->posts_count);

        Event::off(PostForm::class, PostForm::EVENT_BEFORE_CREATING, $handler);
    }

    public function testCreateLoadFalse(): void
    {
        $this->assertFalse($this->podium()->post->create([], Member::findOne(1), Thread::findOne(1))->result);
    }

    public function testFailedCreateValidate(): void
    {
        $mock = $this->getMockBuilder(PostForm::class)->setMethods(['validate'])->getMock();
        $mock->method('validate')->willReturn(false);

        $this->assertFalse($mock->create()->result);
    }

    public function testFailedCreate(): void
    {
        $mock = $this->getMockBuilder(PostForm::class)->setMethods(['save'])->getMock();
        $mock->method('save')->willReturn(false);

        $this->assertFalse($mock->create()->result);
    }

    public function testUpdate(): void
    {
        Event::on(PostForm::class, PostForm::EVENT_BEFORE_EDITING, function () {
            $this->eventsRaised[PostForm::EVENT_BEFORE_EDITING] = true;
        });
        Event::on(PostForm::class, PostForm::EVENT_AFTER_EDITING, function () {
            $this->eventsRaised[PostForm::EVENT_AFTER_EDITING] = true;
        });

        $data = [
            'id' => 1,
            'content' => 'post-updated',
        ];

        $response = $this->podium()->post->edit($data);
        $time = time();

        $this->assertTrue($response->result);
        $this->assertEquals([
            'id' => 1,
            'category_id' => 1,
            'forum_id' => 1,
            'thread_id' => 1,
            'author_id' => 1,
            'content' => 'post-updated',
            'type_id' => 'post',
            'created_at' => 1,
            'updated_at' => $time,
            'edited' => true,
            'likes' => 0,
            'dislikes' => 0,
            'edited_at' => $time,
            'archived' => 0,
        ], $response->data);

        $post = PostRepo::findOne(['content' => 'post-updated']);
        $this->assertEquals(array_merge($data, [
            'author_id' => 1,
            'category_id' => 1,
            'forum_id' => 1,
            'thread_id' => 1,
            'edited' => true,
            'likes' => 0,
            'dislikes' => 0,
            'edited_at' => time(),
            'type_id' => PostType::POST,
        ]), [
            'id' => $post->id,
            'content' => $post->content,
            'author_id' => $post->author_id,
            'category_id' => $post->category_id,
            'forum_id' => $post->forum_id,
            'thread_id' => $post->thread_id,
            'edited' => $post->edited,
            'likes' => $post->likes,
            'dislikes' => $post->dislikes,
            'edited_at' => $post->edited_at,
            'type_id' => $post->type_id,
        ]);
        $this->assertEmpty(PostRepo::findOne(['content' => 'post1']));

        $this->assertEquals(2, ThreadRepo::findOne(1)->posts_count);
        $this->assertEquals(3, ForumRepo::findOne(1)->posts_count);

        $this->assertArrayHasKey(PostForm::EVENT_BEFORE_EDITING, $this->eventsRaised);
        $this->assertArrayHasKey(PostForm::EVENT_AFTER_EDITING, $this->eventsRaised);
    }

    public function testUpdateEventPreventing(): void
    {
        $handler = function ($event) {
            $event->canEdit = false;
        };
        Event::on(PostForm::class, PostForm::EVENT_BEFORE_EDITING, $handler);

        $data = [
            'id' => 1,
            'content' => 'post-updated',
        ];
        $this->assertFalse($this->podium()->post->edit($data)->result);

        $this->assertNotEmpty(PostRepo::findOne(['content' => 'post1']));
        $this->assertEmpty(PostRepo::findOne(['content' => 'post-updated']));

        Event::off(PostForm::class, PostForm::EVENT_BEFORE_EDITING, $handler);
    }

    public function testUpdateLoadFalse(): void
    {
        $this->assertFalse($this->podium()->post->edit(['id' => 1])->result);
    }

    public function testFailedEdit(): void
    {
        $mock = $this->getMockBuilder(PostForm::class)->setMethods(['save'])->getMock();
        $mock->method('save')->willReturn(false);

        $this->assertFalse($mock->edit()->result);
    }

    public function testUpdateNoId(): void
    {
        $this->expectException(InsufficientDataException::class);
        $this->podium()->post->edit([]);
    }

    public function testUpdateWrongId(): void
    {
        $this->expectException(ModelNotFoundException::class);
        $this->podium()->post->edit(['id' => 10000]);
    }

    public function testSetCategory(): void
    {
        $this->expectException(NotSupportedException::class);
        (new PostForm())->setCategory(Category::findOne(1));
    }

    public function testSetForum(): void
    {
        $this->expectException(NotSupportedException::class);
        (new PostForm())->setForum(Forum::findOne(1));
    }

    /**
     * @runInSeparateProcess
     * Keep last in class
     */
    public function testAttributeLabels(): void
    {
        $this->assertEquals(['content' => 'post.content'], (new PostForm())->attributeLabels());
    }
}
