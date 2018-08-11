<?php

declare(strict_types=1);

namespace bizley\podium\tests\base;

use bizley\podium\api\enums\MemberStatus;
use bizley\podium\api\models\forum\Forum;
use bizley\podium\api\models\member\Member;
use bizley\podium\api\models\thread\ThreadForm;
use bizley\podium\api\repos\ThreadRepo;
use bizley\podium\tests\DbTestCase;
use yii\base\Event;

/**
 * Class ThreadFormTest
 * @package bizley\podium\tests\base
 */
class ThreadFormTest extends DbTestCase
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
    ];

    /**
     * @var array
     */
    protected $eventsRaised = [];

    public function testCreate(): void
    {
        Event::on(ThreadForm::class, ThreadForm::EVENT_BEFORE_CREATING, function () {
            $this->eventsRaised[ThreadForm::EVENT_BEFORE_CREATING] = true;
        });
        Event::on(ThreadForm::class, ThreadForm::EVENT_AFTER_CREATING, function () {
            $this->eventsRaised[ThreadForm::EVENT_AFTER_CREATING] = true;
        });

        $data = ['name' => 'thread-new'];
        $this->assertTrue($this->podium()->thread->create($data, Member::findOne(1), Forum::findOne(1)));

        $thread = ThreadRepo::findOne(['name' => 'thread-new']);
        $this->assertEquals(array_merge($data, [
            'slug' => 'thread-new',
            'author_id' => 1,
            'category_id' => 1,
            'forum_id' => 1,
            'posts_count' => 0,
            'views_count' => 0,
        ]), [
            'name' => $thread->name,
            'slug' => $thread->slug,
            'author_id' => $thread->author_id,
            'category_id' => $thread->category_id,
            'forum_id' => $thread->forum_id,
            'views_count' => $thread->views_count,
            'posts_count' => $thread->posts_count,
        ]);

        $this->assertArrayHasKey(ThreadForm::EVENT_BEFORE_CREATING, $this->eventsRaised);
        $this->assertArrayHasKey(ThreadForm::EVENT_AFTER_CREATING, $this->eventsRaised);
    }

    public function testCreateEventPreventing(): void
    {
        $handler = function ($event) {
            $event->canCreate = false;
        };
        Event::on(ThreadForm::class, ThreadForm::EVENT_BEFORE_CREATING, $handler);

        $data = ['name' => 'thread-new'];
        $this->assertFalse($this->podium()->thread->create($data, Member::findOne(1), Forum::findOne(1)));

        $this->assertEmpty(ThreadRepo::findOne(['name' => 'thread-new']));

        Event::off(ThreadForm::class, ThreadForm::EVENT_BEFORE_CREATING, $handler);
    }

    public function testUpdate(): void
    {
        Event::on(ThreadForm::class, ThreadForm::EVENT_BEFORE_EDITING, function () {
            $this->eventsRaised[ThreadForm::EVENT_BEFORE_EDITING] = true;
        });
        Event::on(ThreadForm::class, ThreadForm::EVENT_AFTER_EDITING, function () {
            $this->eventsRaised[ThreadForm::EVENT_AFTER_EDITING] = true;
        });

        $data = ['name' => 'thread-updated'];
        $this->assertTrue($this->podium()->thread->edit(ThreadForm::findOne(1),  $data));

        $thread = ThreadRepo::findOne(['name' => 'thread-updated']);
        $this->assertEquals(array_merge($data, [
            'slug' => 'thread-updated',
            'author_id' => 1,
            'category_id' => 1,
            'forum_id' => 1,
            'views_count' => 0,
            'posts_count' => 0,
        ]), [
            'name' => $thread->name,
            'slug' => $thread->slug,
            'author_id' => $thread->author_id,
            'category_id' => $thread->category_id,
            'forum_id' => $thread->forum_id,
            'views_count' => $thread->views_count,
            'posts_count' => $thread->posts_count,
        ]);
        $this->assertEmpty(ThreadRepo::findOne(['name' => 'thread1']));

        $this->assertArrayHasKey(ThreadForm::EVENT_BEFORE_EDITING, $this->eventsRaised);
        $this->assertArrayHasKey(ThreadForm::EVENT_AFTER_EDITING, $this->eventsRaised);
    }

    public function testUpdateEventPreventing(): void
    {
        $handler = function ($event) {
            $event->canEdit = false;
        };
        Event::on(ThreadForm::class, ThreadForm::EVENT_BEFORE_EDITING, $handler);

        $data = ['name' => 'thread-updated'];
        $this->assertFalse($this->podium()->thread->edit(ThreadForm::findOne(1),  $data));

        $this->assertNotEmpty(ThreadRepo::findOne(['name' => 'thread1']));
        $this->assertEmpty(ThreadRepo::findOne(['name' => 'thread-updated']));

        Event::off(ThreadForm::class, ThreadForm::EVENT_BEFORE_EDITING, $handler);
    }
}
