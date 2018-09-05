<?php

declare(strict_types=1);

namespace bizley\podium\tests\category;

use bizley\podium\api\enums\MemberStatus;
use bizley\podium\api\models\category\Category;
use bizley\podium\tests\DbTestCase;
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
        $category = $this->podium()->category->getCategoryById(1);
        $this->assertEquals(1, $category->getId());
    }

    public function testNonExistingCategory(): void
    {
        $this->assertEmpty($this->podium()->category->getCategoryById(999));
    }

    public function testGetCategoriesByFilterEmpty(): void
    {
        $categories = $this->podium()->category->getCategories();
        $this->assertEquals(2, $categories->getTotalCount());
        $this->assertEquals([1, 2], $categories->getKeys());
    }

    public function testGetCategoriesByFilter(): void
    {
        $filter = new ActiveDataFilter([
            'searchModel' => function () {
                return (new \yii\base\DynamicModel(['id']))->addRule('id', 'integer');
            }
        ]);
        $filter->load(['filter' => ['id' => 2]], '');
        $categories = $this->podium()->category->getCategories($filter);
        $this->assertEquals(1, $categories->getTotalCount());
        $this->assertEquals([2], $categories->getKeys());
    }

    public function testGetParent(): void
    {
        $this->expectException(NotSupportedException::class);
        (new Category())->getParent();
    }

    public function testGetPostsCount(): void
    {
        $this->expectException(NotSupportedException::class);
        (new Category())->getPostsCount();
    }

    public function testIsArchived(): void
    {
        $this->assertTrue($this->podium()->category->getCategoryById(1)->isArchived());
        $this->assertFalse($this->podium()->category->getCategoryById(2)->isArchived());
    }
}
