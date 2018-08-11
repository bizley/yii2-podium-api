<?php

declare(strict_types=1);

namespace bizley\podium\tests\base;

use bizley\podium\api\enums\MemberStatus;
use bizley\podium\api\models\member\Member;
use bizley\podium\api\models\post\PostForm;
use bizley\podium\api\models\thread\Thread;
use bizley\podium\api\repos\PostRepo;
use bizley\podium\tests\DbTestCase;
use yii\base\Event;

/**
 * Class PostFormTest
 * @package bizley\podium\tests\base
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
        $this->assertTrue($this->podium()->post->create($data, Member::findOne(1), Thread::findOne(1)));

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
        ]);

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
        $this->assertFalse($this->podium()->post->create($data, Member::findOne(1), Thread::findOne(1)));

        $this->assertEmpty(PostRepo::findOne(['content' => 'post-new']));

        Event::off(PostForm::class, PostForm::EVENT_BEFORE_CREATING, $handler);
    }

    public function testUpdate(): void
    {
        Event::on(PostForm::class, PostForm::EVENT_BEFORE_EDITING, function () {
            $this->eventsRaised[PostForm::EVENT_BEFORE_EDITING] = true;
        });
        Event::on(PostForm::class, PostForm::EVENT_AFTER_EDITING, function () {
            $this->eventsRaised[PostForm::EVENT_AFTER_EDITING] = true;
        });

        $data = ['content' => 'post-updated'];
        $this->assertTrue($this->podium()->post->edit(PostForm::findOne(1),  $data));

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
        ]);
        $this->assertEmpty(PostRepo::findOne(['content' => 'post1']));

        $this->assertArrayHasKey(PostForm::EVENT_BEFORE_EDITING, $this->eventsRaised);
        $this->assertArrayHasKey(PostForm::EVENT_AFTER_EDITING, $this->eventsRaised);
    }

    public function testUpdateEventPreventing(): void
    {
        $handler = function ($event) {
            $event->canEdit = false;
        };
        Event::on(PostForm::class, PostForm::EVENT_BEFORE_EDITING, $handler);

        $data = ['content' => 'post-updated'];
        $this->assertFalse($this->podium()->post->edit(PostForm::findOne(1),  $data));

        $this->assertNotEmpty(PostRepo::findOne(['content' => 'post1']));
        $this->assertEmpty(PostRepo::findOne(['content' => 'post-updated']));

        Event::off(PostForm::class, PostForm::EVENT_BEFORE_EDITING, $handler);
    }
}
