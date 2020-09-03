<?php

declare(strict_types=1);

namespace bizley\podium\tests\category;

use bizley\podium\api\components\ModelNotFoundException;
use bizley\podium\api\enums\MemberStatus;
use bizley\podium\api\models\category\CategoryRemover;
use bizley\podium\api\repos\CategoryRepo;
use bizley\podium\api\repos\ForumRepo;
use bizley\podium\api\repos\PostRepo;
use bizley\podium\api\repos\ThreadRepo;
use bizley\podium\tests\DbTestCase;
use Exception;
use yii\base\Event;

/**
 * Class CategoryRemoverTest
 * @package bizley\podium\tests\category
 */
class CategoryRemoverTest extends DbTestCase
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
                'archived' => true,
            ],
            [
                'id' => 2,
                'author_id' => 1,
                'name' => 'category2',
                'slug' => 'category2',
                'created_at' => 1,
                'updated_at' => 1,
                'archived' => false,
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

    /**
     * @throws ModelNotFoundException
     */
    public function testRemove(): void
    {
        Event::on(CategoryRemover::class, CategoryRemover::EVENT_BEFORE_REMOVING, function () {
            $this->eventsRaised[CategoryRemover::EVENT_BEFORE_REMOVING] = true;
        });
        Event::on(CategoryRemover::class, CategoryRemover::EVENT_AFTER_REMOVING, function () {
            $this->eventsRaised[CategoryRemover::EVENT_AFTER_REMOVING] = true;
        });

        $this->assertTrue($this->podium()->category->remove(1)->result);

        $this->assertEmpty(CategoryRepo::findOne(1));
        $this->assertEmpty(ForumRepo::findOne(1));
        $this->assertEmpty(ThreadRepo::findOne(1));
        $this->assertEmpty(PostRepo::findOne(1));

        $this->assertArrayHasKey(CategoryRemover::EVENT_BEFORE_REMOVING, $this->eventsRaised);
        $this->assertArrayHasKey(CategoryRemover::EVENT_AFTER_REMOVING, $this->eventsRaised);
    }

    /**
     * @throws ModelNotFoundException
     */
    public function testRemoveEventPreventing(): void
    {
        $handler = static function ($event) {
            $event->canRemove = false;
        };
        Event::on(CategoryRemover::class, CategoryRemover::EVENT_BEFORE_REMOVING, $handler);

        $this->assertFalse($this->podium()->category->remove(1)->result);

        $this->assertNotEmpty(CategoryRepo::findOne(1));
        $this->assertNotEmpty(ForumRepo::findOne(1));
        $this->assertNotEmpty(ThreadRepo::findOne(1));
        $this->assertNotEmpty(PostRepo::findOne(1));

        Event::off(CategoryRemover::class, CategoryRemover::EVENT_BEFORE_REMOVING, $handler);
    }

    /**
     * @throws ModelNotFoundException
     */
    public function testNonArchived(): void
    {
        $this->assertFalse($this->podium()->category->remove(2)->result);
        $this->assertNotEmpty(CategoryRepo::findOne(2));
    }

    public function testFailedDelete(): void
    {
        $mock = $this->getMockBuilder(CategoryRemover::class)->setMethods(['delete'])->getMock();
        $mock->method('delete')->willReturn(false);
        $mock->archived = true;

        $this->assertFalse($mock->remove()->result);
    }

    public function testExceptionDelete(): void
    {
        $mock = $this->getMockBuilder(CategoryRemover::class)->setMethods(['delete'])->getMock();
        $mock->method('delete')->will($this->throwException(new Exception()));
        $mock->archived = true;

        $this->assertFalse($mock->remove()->result);
    }

    /**
     * @throws ModelNotFoundException
     */
    public function testNoCategoryToRemove(): void
    {
        $this->expectException(ModelNotFoundException::class);
        $this->podium()->category->remove(999);
    }
}
