<?php

declare(strict_types=1);

namespace bizley\podium\tests\forum;

use bizley\podium\api\enums\MemberStatus;
use bizley\podium\api\models\category\Category;
use bizley\podium\api\models\forum\ForumSorter;
use bizley\podium\api\repos\ForumRepo;
use bizley\podium\tests\DbTestCase;
use yii\base\Event;

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

        $this->assertTrue($this->podium()->forum->sort(Category::findOne(1), [3, 1, 2])->result);

        $this->assertEquals(0, ForumRepo::findOne(3)->sort);
        $this->assertEquals(1, ForumRepo::findOne(1)->sort);
        $this->assertEquals(2, ForumRepo::findOne(2)->sort);

        $this->assertArrayHasKey(ForumSorter::EVENT_BEFORE_SORTING, $this->eventsRaised);
        $this->assertArrayHasKey(ForumSorter::EVENT_AFTER_SORTING, $this->eventsRaised);
    }

    public function testSortEventPreventing(): void
    {
        $handler = function ($event) {
            $event->canSort = false;
        };
        Event::on(ForumSorter::class, ForumSorter::EVENT_BEFORE_SORTING, $handler);

        $this->assertFalse($this->podium()->forum->sort(Category::findOne(1), [3, 1, 2])->result);

        $this->assertEquals(-9, ForumRepo::findOne(3)->sort);
        $this->assertEquals(8, ForumRepo::findOne(1)->sort);
        $this->assertEquals(4, ForumRepo::findOne(2)->sort);

        Event::off(ForumSorter::class, ForumSorter::EVENT_BEFORE_SORTING, $handler);
    }

    public function testSortLoadFalse(): void
    {
        $this->assertFalse($this->podium()->forum->sort(Category::findOne(1), [])->result);
    }

    public function testSortWrongDataType(): void
    {
        $this->assertFalse($this->podium()->forum->sort(Category::findOne(1), ['aaa'])->result);
    }

    public function testSortWrongDataId(): void
    {
        $this->assertFalse($this->podium()->forum->sort(Category::findOne(1), [99])->result);
    }

    public function testSortForumsInWrongCategory(): void
    {
        $this->assertTrue($this->podium()->forum->sort(Category::findOne(2), [3, 1, 2])->result);

        $this->assertEquals(-9, ForumRepo::findOne(3)->sort);
        $this->assertEquals(8, ForumRepo::findOne(1)->sort);
        $this->assertEquals(4, ForumRepo::findOne(2)->sort);
    }

    public function testSortWithOutsideForum(): void
    {
        $this->assertTrue($this->podium()->forum->sort(Category::findOne(1), [3, 1, 4])->result);

        $this->assertEquals(0, ForumRepo::findOne(3)->sort);
        $this->assertEquals(1, ForumRepo::findOne(1)->sort);
        $this->assertEquals(100, ForumRepo::findOne(4)->sort);
    }
}
