<?php

declare(strict_types=1);

namespace bizley\podium\tests\category;

use bizley\podium\api\enums\MemberStatus;
use bizley\podium\api\models\category\Category;
use bizley\podium\api\models\category\CategoryRemover;
use bizley\podium\api\models\forum\ForumMover;
use bizley\podium\tests\DbTestCase;
use yii\base\DynamicModel;
use yii\base\InvalidArgumentException;
use yii\base\InvalidConfigException;
use yii\base\NotSupportedException;
use yii\data\ActiveDataFilter;

/**
 * Class CategoryTest
 * @package bizley\podium\tests\category
 */
class CategoryTest extends DbTestCase
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
    ];

    public function testGetCategoryById(): void
    {
        $category = $this->podium()->category->getById(1);
        $this->assertEquals(1, $category->getId());
        $this->assertEquals(1, $category->getCreatedAt());
    }

    public function testNonExistingCategory(): void
    {
        $this->assertEmpty($this->podium()->category->getById(999));
    }

    public function testGetCategoriesByFilterEmpty(): void
    {
        $categories = $this->podium()->category->getAll();
        $this->assertEquals(2, $categories->getTotalCount());
        $this->assertEquals([1, 2], $categories->getKeys());
    }

    public function testGetCategoriesByFilter(): void
    {
        $filter = new ActiveDataFilter([
            'searchModel' => static function () {
                return (new DynamicModel(['id']))->addRule('id', 'integer');
            }
        ]);
        $filter->load(['filter' => ['id' => 2]], '');

        $categories = $this->podium()->category->getAll($filter);

        $this->assertEquals(1, $categories->getTotalCount());
        $this->assertEquals([2], $categories->getKeys());
    }

    public function testGetCategoriesByFilterWithSorter(): void
    {
        $categories = $this->podium()->category->getAll(null, ['defaultOrder' => ['id' => SORT_DESC]]);
        $this->assertEquals(2, $categories->getTotalCount());
        $this->assertEquals([2, 1], $categories->getKeys());
    }

    public function testGetCategoriesByFilterWithPagination(): void
    {
        $categories = $this->podium()->category->getAll(null, null, ['defaultPageSize' => 1]);
        $this->assertEquals(2, $categories->getTotalCount());
        $this->assertEquals([1], $categories->getKeys());
    }

    /**
     * @throws NotSupportedException
     */
    public function testGetParent(): void
    {
        $this->expectException(NotSupportedException::class);
        (new Category())->getParent();
    }

    /**
     * @throws NotSupportedException
     */
    public function testGetPostsCount(): void
    {
        $this->expectException(NotSupportedException::class);
        (new Category())->getPostsCount();
    }

    public function testIsArchived(): void
    {
        $this->assertTrue($this->podium()->category->getById(1)->isArchived());
        $this->assertFalse($this->podium()->category->getById(2)->isArchived());
    }

    /**
     * @throws InvalidConfigException
     */
    public function testConvert(): void
    {
        $category = Category::findById(1);
        $this->assertInstanceOf(CategoryRemover::class, $category->convert(CategoryRemover::class));
    }

    /**
     * @throws InvalidConfigException
     */
    public function testWrongConvert(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $category = Category::findById(1);
        $category->convert('stdClass');
    }
}
