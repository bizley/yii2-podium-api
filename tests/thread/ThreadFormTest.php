<?php

declare(strict_types=1);

namespace bizley\podium\tests\thread;

use bizley\podium\api\base\ModelNotFoundException;
use bizley\podium\api\enums\MemberStatus;
use bizley\podium\api\InsufficientDataException;
use bizley\podium\api\models\category\Category;
use bizley\podium\api\models\forum\Forum;
use bizley\podium\api\models\member\Member;
use bizley\podium\api\models\thread\Thread;
use bizley\podium\api\models\thread\ThreadForm;
use bizley\podium\api\repos\ForumRepo;
use bizley\podium\api\repos\ThreadRepo;
use bizley\podium\tests\DbTestCase;
use yii\base\Event;
use yii\base\NotSupportedException;
use yii\helpers\ArrayHelper;

use function array_merge;
use function time;

/**
 * Class ThreadFormTest
 * @package bizley\podium\tests\thread
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
                'threads_count' => 5,
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

        $response = $this->podium()->thread->create($data, Member::findOne(1), Forum::findOne(1));
        $time = time();

        self::assertTrue($response->result);

        $responseData = $response->data;
        $createdAt = ArrayHelper::remove($responseData, 'created_at');
        $updatedAt = ArrayHelper::remove($responseData, 'updated_at');

        self::assertLessThanOrEqual($time, $createdAt);
        self::assertLessThanOrEqual($time, $updatedAt);

        self::assertEquals([
            'id' => 2,
            'category_id' => 1,
            'forum_id' => 1,
            'name' => 'thread-new',
            'slug' => 'thread-new',
            'author_id' => 1,
        ], $responseData);

        $thread = ThreadRepo::findOne(['name' => 'thread-new']);
        self::assertEquals(array_merge($data, [
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

        self::assertEquals(6, ForumRepo::findOne(1)->threads_count);

        self::assertArrayHasKey(ThreadForm::EVENT_BEFORE_CREATING, $this->eventsRaised);
        self::assertArrayHasKey(ThreadForm::EVENT_AFTER_CREATING, $this->eventsRaised);
    }

    public function testCreateWithSlug(): void
    {
        $data = [
            'name' => 'thread-new-with-slug',
            'slug' => 'thr-slug',
        ];
        self::assertTrue($this->podium()->thread->create($data, Member::findOne(1), Forum::findOne(1))->result);

        $thread = ThreadRepo::findOne(['name' => 'thread-new-with-slug']);
        self::assertEquals($data, [
            'name' => $thread->name,
            'slug' => $thread->slug,
        ]);
    }

    public function testCreateEventPreventing(): void
    {
        $handler = static function ($event) {
            $event->canCreate = false;
        };
        Event::on(ThreadForm::class, ThreadForm::EVENT_BEFORE_CREATING, $handler);

        $data = ['name' => 'thread-new'];
        self::assertFalse($this->podium()->thread->create($data, Member::findOne(1), Forum::findOne(1))->result);

        self::assertEmpty(ThreadRepo::findOne(['name' => 'thread-new']));

        self::assertEquals(5, ForumRepo::findOne(1)->threads_count);

        Event::off(ThreadForm::class, ThreadForm::EVENT_BEFORE_CREATING, $handler);
    }

    public function testCreateLoadFalse(): void
    {
        self::assertFalse($this->podium()->thread->create([], Member::findOne(1), Forum::findOne(1))->result);
    }

    public function testFailedCreateValidate(): void
    {
        $mock = $this->getMockBuilder(ThreadForm::class)->setMethods(['validate'])->getMock();
        $mock->method('validate')->willReturn(false);

        self::assertFalse($mock->create()->result);
    }

    public function testFailedCreate(): void
    {
        $mock = $this->getMockBuilder(ThreadForm::class)->setMethods(['save'])->getMock();
        $mock->method('save')->willReturn(false);

        self::assertFalse($mock->create()->result);
    }

    /**
     * @throws InsufficientDataException
     * @throws ModelNotFoundException
     */
    public function testUpdate(): void
    {
        Event::on(ThreadForm::class, ThreadForm::EVENT_BEFORE_EDITING, function () {
            $this->eventsRaised[ThreadForm::EVENT_BEFORE_EDITING] = true;
        });
        Event::on(ThreadForm::class, ThreadForm::EVENT_AFTER_EDITING, function () {
            $this->eventsRaised[ThreadForm::EVENT_AFTER_EDITING] = true;
        });

        $data = [
            'id' => 1,
            'name' => 'thread-updated',
        ];

        $response = $this->podium()->thread->edit($data);
        $time = time();

        self::assertTrue($response->result);

        $responseData = $response->data;
        $updatedAt = ArrayHelper::remove($responseData, 'updated_at');

        self::assertLessThanOrEqual($time, $updatedAt);

        self::assertEquals([
            'id' => 1,
            'category_id' => 1,
            'forum_id' => 1,
            'name' => 'thread-updated',
            'slug' => 'thread1',
            'author_id' => 1,
            'created_at' => 1,
            'archived' => 0,
            'views_count' => 0,
            'posts_count' => 0,
            'pinned' => 0,
            'locked' => 0,
            'created_post_at' => null,
            'updated_post_at' => null,
        ], $responseData);

        $thread = ThreadRepo::findOne(['name' => 'thread-updated']);
        self::assertEquals(array_merge($data, [
            'slug' => 'thread1',
            'author_id' => 1,
            'category_id' => 1,
            'forum_id' => 1,
            'views_count' => 0,
            'posts_count' => 0,
        ]), [
            'id' => $thread->id,
            'name' => $thread->name,
            'slug' => $thread->slug,
            'author_id' => $thread->author_id,
            'category_id' => $thread->category_id,
            'forum_id' => $thread->forum_id,
            'views_count' => $thread->views_count,
            'posts_count' => $thread->posts_count,
        ]);
        self::assertEmpty(ThreadRepo::findOne(['name' => 'thread1']));

        self::assertEquals(5, ForumRepo::findOne(1)->threads_count);

        self::assertArrayHasKey(ThreadForm::EVENT_BEFORE_EDITING, $this->eventsRaised);
        self::assertArrayHasKey(ThreadForm::EVENT_AFTER_EDITING, $this->eventsRaised);
    }

    /**
     * @throws InsufficientDataException
     * @throws ModelNotFoundException
     */
    public function testUpdateEventPreventing(): void
    {
        $handler = static function ($event) {
            $event->canEdit = false;
        };
        Event::on(ThreadForm::class, ThreadForm::EVENT_BEFORE_EDITING, $handler);

        $data = [
            'id' => 1,
            'name' => 'thread-updated',
        ];
        self::assertFalse($this->podium()->thread->edit($data)->result);

        self::assertNotEmpty(ThreadRepo::findOne(['name' => 'thread1']));
        self::assertEmpty(ThreadRepo::findOne(['name' => 'thread-updated']));

        Event::off(ThreadForm::class, ThreadForm::EVENT_BEFORE_EDITING, $handler);
    }

    /**
     * @throws InsufficientDataException
     * @throws ModelNotFoundException
     */
    public function testUpdateLoadFalse(): void
    {
        self::assertFalse($this->podium()->thread->edit(['id' => 1])->result);
    }

    public function testFailedEdit(): void
    {
        $mock = $this->getMockBuilder(ThreadForm::class)->setMethods(['save'])->getMock();
        $mock->method('save')->willReturn(false);

        self::assertFalse($mock->edit()->result);
    }

    /**
     * @throws NotSupportedException
     */
    public function testSetCategory(): void
    {
        $this->expectException(NotSupportedException::class);
        (new ThreadForm())->setCategory(Category::findOne(1));
    }

    /**
     * @throws NotSupportedException
     */
    public function testSetThread(): void
    {
        $this->expectException(NotSupportedException::class);
        (new ThreadForm())->setThread(Thread::findOne(1));
    }

    /**
     * @throws InsufficientDataException
     * @throws ModelNotFoundException
     */
    public function testUpdateNoId(): void
    {
        $this->expectException(InsufficientDataException::class);
        $this->podium()->thread->edit([]);
    }

    /**
     * @throws InsufficientDataException
     * @throws ModelNotFoundException
     */
    public function testUpdateWrongId(): void
    {
        $this->expectException(ModelNotFoundException::class);
        $this->podium()->thread->edit(['id' => 10000]);
    }

    /**
     * @runInSeparateProcess
     * Keep last in class
     */
    public function testAttributeLabels(): void
    {
        self::assertEquals([
            'name' => 'thread.name',
            'slug' => 'thread.slug',
        ], (new ThreadForm())->attributeLabels());
    }
}
