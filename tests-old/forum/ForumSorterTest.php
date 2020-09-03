<?php

declare(strict_types=1);

namespace bizley\podium\tests\forum;

use bizley\podium\api\enums\MemberStatus;
use bizley\podium\api\models\category\Category;
use bizley\podium\api\models\forum\Forum;
use bizley\podium\api\models\forum\ForumSorter;
use bizley\podium\api\models\thread\Thread;
use bizley\podium\api\repos\ForumRepo;
use bizley\podium\tests\DbTestCase;
use yii\base\Event;
use yii\base\NotSupportedException;

/**
 * Class ForumSorterTest
 * @package bizley\podium\tests\forum
 */
class ForumSorterTest extends DbTestCase
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
                'sort' => 10,
                'created_at' => 1,
                'updated_at' => 1,
            ],
            [
                'id' => 2,
                'author_id' => 1,
                'name' => 'category2',
                'slug' => 'category2',
                'sort' => 1,
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
                'sort' => 8,
                'created_at' => 1,
                'updated_at' => 1,
            ],
            [
                'id' => 2,
                'category_id' => 1,
                'author_id' => 1,
                'name' => 'forum2',
                'slug' => 'forum2',
                'sort' => 4,
                'created_at' => 1,
                'updated_at' => 1,
            ],
            [
                'id' => 3,
                'category_id' => 1,
                'author_id' => 1,
                'name' => 'forum3',
                'slug' => 'forum3',
                'sort' => -9,
                'created_at' => 1,
                'updated_at' => 1,
            ],
            [
                'id' => 4,
                'category_id' => 2,
                'author_id' => 1,
                'name' => 'forum4',
                'slug' => 'forum4',
                'sort' => 100,
                'created_at' => 1,
                'updated_at' => 1,
            ],
        ],
    ];

    /**
     * @var array
     */
    protected $eventsRaised = [];

    public function testSort(): void
    {
        Event::on(ForumSorter::class, ForumSorter::EVENT_BEFORE_SORTING, function () {
            $this->eventsRaised[ForumSorter::EVENT_BEFORE_SORTING] = true;
        });
        Event::on(ForumSorter::class, ForumSorter::EVENT_AFTER_SORTING, function () {
            $this->eventsRaised[ForumSorter::EVENT_AFTER_SORTING] = true;
        });

        self::assertTrue($this->podium()->forum->sort(Category::findOne(1), [3, 1, 2])->result);

        self::assertEquals(0, ForumRepo::findOne(3)->sort);
        self::assertEquals(1, ForumRepo::findOne(1)->sort);
        self::assertEquals(2, ForumRepo::findOne(2)->sort);

        self::assertArrayHasKey(ForumSorter::EVENT_BEFORE_SORTING, $this->eventsRaised);
        self::assertArrayHasKey(ForumSorter::EVENT_AFTER_SORTING, $this->eventsRaised);
    }

    public function testSortEventPreventing(): void
    {
        $handler = static function ($event) {
            $event->canSort = false;
        };
        Event::on(ForumSorter::class, ForumSorter::EVENT_BEFORE_SORTING, $handler);

        self::assertFalse($this->podium()->forum->sort(Category::findOne(1), [3, 1, 2])->result);

        self::assertEquals(-9, ForumRepo::findOne(3)->sort);
        self::assertEquals(8, ForumRepo::findOne(1)->sort);
        self::assertEquals(4, ForumRepo::findOne(2)->sort);

        Event::off(ForumSorter::class, ForumSorter::EVENT_BEFORE_SORTING, $handler);
    }

    public function testSortLoadFalse(): void
    {
        self::assertFalse($this->podium()->forum->sort(Category::findOne(1))->result);
    }

    public function testSortWrongDataType(): void
    {
        self::assertFalse($this->podium()->forum->sort(Category::findOne(1), ['aaa'])->result);
    }

    public function testSortWrongDataId(): void
    {
        self::assertFalse($this->podium()->forum->sort(Category::findOne(1), [99])->result);
    }

    public function testSortForumsInWrongCategory(): void
    {
        self::assertTrue($this->podium()->forum->sort(Category::findOne(2), [3, 1, 2])->result);

        self::assertEquals(-9, ForumRepo::findOne(3)->sort);
        self::assertEquals(8, ForumRepo::findOne(1)->sort);
        self::assertEquals(4, ForumRepo::findOne(2)->sort);
    }

    public function testSortWithOutsideForum(): void
    {
        self::assertTrue($this->podium()->forum->sort(Category::findOne(1), [3, 1, 4])->result);

        self::assertEquals(0, ForumRepo::findOne(3)->sort);
        self::assertEquals(1, ForumRepo::findOne(1)->sort);
        self::assertEquals(100, ForumRepo::findOne(4)->sort);
    }

    /**
     * @throws NotSupportedException
     */
    public function testSetForum(): void
    {
        $this->expectException(NotSupportedException::class);
        (new ForumSorter())->setForum(new Forum());
    }

    /**
     * @throws NotSupportedException
     */
    public function testSetThread(): void
    {
        $this->expectException(NotSupportedException::class);
        (new ForumSorter())->setThread(new Thread());
    }

    public function testExceptionSort(): void
    {
        $mock = $this->getMockBuilder(ForumSorter::class)->setMethods(['afterSort'])->getMock();
        $mock->method('afterSort')->will(self::throwException(new \Exception()));
        $mock->sortOrder = [1];

        self::assertFalse($mock->sort()->result);
    }
}
