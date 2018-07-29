<?php

declare(strict_types=1);

namespace bizley\podium\tests\base;

use bizley\podium\api\enums\MemberStatus;
use bizley\podium\api\models\category\CategoryForm;
use bizley\podium\api\models\member\Member;
use bizley\podium\api\repos\CategoryRepo;
use bizley\podium\tests\DbTestCase;
use yii\base\Event;

/**
 * Class CategoryFormTest
 * @package bizley\podium\tests\base
 */
class CategoryFormTest extends DbTestCase
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
    ];

    /**
     * @var array
     */
    protected $eventsRaised = [];

    /**
     * @throws \yii\db\Exception
     */
    protected function setUp(): void
    {
        $this->fixturesUp();
    }

    /**
     * @throws \yii\db\Exception
     */
    protected function tearDown(): void
    {
        $this->fixturesDown();
    }

    public function testCreate(): void
    {
        Event::on(CategoryForm::class, CategoryForm::EVENT_BEFORE_CREATING, function () {
            $this->eventsRaised[CategoryForm::EVENT_BEFORE_CREATING] = true;
        });
        Event::on(CategoryForm::class, CategoryForm::EVENT_AFTER_CREATING, function () {
            $this->eventsRaised[CategoryForm::EVENT_AFTER_CREATING] = true;
        });

        $data = [
            'name' => 'category-new',
            'visible' => 1,
            'sort' => 10,
        ];
        $this->assertTrue($this->podium()->category->create($data, Member::findOne(1)));

        $category = CategoryRepo::findOne(['name' => 'category-new']);
        $this->assertEquals(array_merge($data, [
            'slug' => 'category-new',
            'author_id' => 1,
        ]), [
            'name' => $category->name,
            'visible' => $category->visible,
            'sort' => $category->sort,
            'slug' => $category->slug,
            'author_id' => $category->author_id,
        ]);

        $this->assertArrayHasKey(CategoryForm::EVENT_BEFORE_CREATING, $this->eventsRaised);
        $this->assertArrayHasKey(CategoryForm::EVENT_AFTER_CREATING, $this->eventsRaised);
    }

    public function testCreateEventPreventing(): void
    {
        $handler = function ($event) {
            $event->canCreate = false;
        };
        Event::on(CategoryForm::class, CategoryForm::EVENT_BEFORE_CREATING, $handler);

        $data = [
            'name' => 'category-new',
            'visible' => 1,
            'sort' => 10,
        ];
        $this->assertFalse($this->podium()->category->create($data, Member::findOne(1)));

        $this->assertEmpty(CategoryRepo::findOne(['name' => 'category-new']));

        Event::off(CategoryForm::class, CategoryForm::EVENT_BEFORE_CREATING, $handler);
    }

    public function testUpdate(): void
    {
        Event::on(CategoryForm::class, CategoryForm::EVENT_BEFORE_EDITING, function () {
            $this->eventsRaised[CategoryForm::EVENT_BEFORE_EDITING] = true;
        });
        Event::on(CategoryForm::class, CategoryForm::EVENT_AFTER_EDITING, function () {
            $this->eventsRaised[CategoryForm::EVENT_AFTER_EDITING] = true;
        });

        $data = [
            'name' => 'category-updated',
            'visible' => 0,
            'sort' => 2,
        ];
        $this->assertTrue($this->podium()->category->edit(CategoryForm::findOne(1),  $data));

        $category = CategoryRepo::findOne(['name' => 'category-updated']);
        $this->assertEquals(array_merge($data, [
            'slug' => 'category-updated',
            'author_id' => 1,
        ]), [
            'name' => $category->name,
            'visible' => $category->visible,
            'sort' => $category->sort,
            'slug' => $category->slug,
            'author_id' => $category->author_id,
        ]);
        $this->assertEmpty(CategoryRepo::findOne(['name' => 'category1']));

        $this->assertArrayHasKey(CategoryForm::EVENT_BEFORE_EDITING, $this->eventsRaised);
        $this->assertArrayHasKey(CategoryForm::EVENT_AFTER_EDITING, $this->eventsRaised);
    }

    public function testUpdateEventPreventing(): void
    {
        $handler = function ($event) {
            $event->canEdit = false;
        };
        Event::on(CategoryForm::class, CategoryForm::EVENT_BEFORE_EDITING, $handler);

        $data = [
            'name' => 'category-updated',
            'visible' => 0,
            'sort' => 2,
        ];
        $this->assertFalse($this->podium()->category->edit(CategoryForm::findOne(1),  $data));

        $this->assertNotEmpty(CategoryRepo::findOne(['name' => 'category1']));
        $this->assertEmpty(CategoryRepo::findOne(['name' => 'category-updated']));

        Event::off(CategoryForm::class, CategoryForm::EVENT_BEFORE_EDITING, $handler);
    }
}
