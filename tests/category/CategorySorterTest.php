<?php

declare(strict_types=1);

namespace bizley\podium\tests\category;

use bizley\podium\api\enums\MemberStatus;
use bizley\podium\api\models\category\Category;
use bizley\podium\api\models\category\CategorySorter;
use bizley\podium\api\models\forum\Forum;
use bizley\podium\api\models\thread\Thread;
use bizley\podium\api\repos\CategoryRepo;
use bizley\podium\tests\DbTestCase;
use Exception;
use yii\base\Event;
use yii\base\NotSupportedException;

/**
 * Class CategorySorterTest
 * @package bizley\podium\tests\category
 */
class CategorySorterTest extends DbTestCase
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
                'sort' => 16,
                'created_at' => 1,
                'updated_at' => 1,
            ],
            [
                'id' => 3,
                'author_id' => 1,
                'name' => 'category3',
                'slug' => 'category3',
                'sort' => -5,
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
        Event::on(CategorySorter::class, CategorySorter::EVENT_BEFORE_SORTING, function () {
            $this->eventsRaised[CategorySorter::EVENT_BEFORE_SORTING] = true;
        });
        Event::on(CategorySorter::class, CategorySorter::EVENT_AFTER_SORTING, function () {
            $this->eventsRaised[CategorySorter::EVENT_AFTER_SORTING] = true;
        });

        $this->assertTrue($this->podium()->category->sort([3, 1, 2])->result);

        $this->assertEquals(0, CategoryRepo::findOne(3)->sort);
        $this->assertEquals(1, CategoryRepo::findOne(1)->sort);
        $this->assertEquals(2, CategoryRepo::findOne(2)->sort);

        $this->assertArrayHasKey(CategorySorter::EVENT_BEFORE_SORTING, $this->eventsRaised);
        $this->assertArrayHasKey(CategorySorter::EVENT_AFTER_SORTING, $this->eventsRaised);
    }

    public function testSortEventPreventing(): void
    {
        $handler = static function ($event) {
            $event->canSort = false;
        };
        Event::on(CategorySorter::class, CategorySorter::EVENT_BEFORE_SORTING, $handler);

        $this->assertFalse($this->podium()->category->sort([3, 1, 2])->result);

        $this->assertEquals(-5, CategoryRepo::findOne(3)->sort);
        $this->assertEquals(10, CategoryRepo::findOne(1)->sort);
        $this->assertEquals(16, CategoryRepo::findOne(2)->sort);

        Event::off(CategorySorter::class, CategorySorter::EVENT_BEFORE_SORTING, $handler);
    }

    public function testSortLoadFalse(): void
    {
        $this->assertFalse($this->podium()->category->sort()->result);
    }

    public function testSortWrongDataType(): void
    {
        $this->assertFalse($this->podium()->category->sort(['aaa'])->result);
    }

    public function testSortWrongDataId(): void
    {
        $this->assertFalse($this->podium()->category->sort([99])->result);
    }

    public function testExceptionSort(): void
    {
        $mock = $this->getMockBuilder(CategorySorter::class)->setMethods(['afterSort'])->getMock();
        $mock->method('afterSort')->will($this->throwException(new Exception()));
        $mock->sortOrder = [1];

        $this->assertFalse($mock->sort()->result);
    }

    /**
     * @throws NotSupportedException
     */
    public function testSetCategory(): void
    {
        $this->expectException(NotSupportedException::class);
        (new CategorySorter())->setCategory(new Category());
    }

    /**
     * @throws NotSupportedException
     */
    public function testSetForum(): void
    {
        $this->expectException(NotSupportedException::class);
        (new CategorySorter())->setForum(new Forum());
    }

    /**
     * @throws NotSupportedException
     */
    public function testSetThread(): void
    {
        $this->expectException(NotSupportedException::class);
        (new CategorySorter())->setThread(new Thread());
    }
}
