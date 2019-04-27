<?php

declare(strict_types=1);

namespace bizley\podium\tests\category;

use bizley\podium\api\base\InsufficientDataException;
use bizley\podium\api\base\ModelNotFoundException;
use bizley\podium\api\enums\MemberStatus;
use bizley\podium\api\models\category\CategoryForm;
use bizley\podium\api\models\member\Member;
use bizley\podium\api\repos\CategoryRepo;
use bizley\podium\tests\DbTestCase;
use yii\base\Event;
use yii\helpers\ArrayHelper;
use function array_merge;
use function time;

/**
 * Class CategoryFormTest
 * @package bizley\podium\tests\category
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
    ];

    /**
     * @var array
     */
    protected $eventsRaised = [];

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
            'description' => 'desc-new',
            'visible' => 1,
            'sort' => 10,
        ];

        $response = $this->podium()->category->create($data, Member::findOne(1));
        $time = time();

        $this->assertTrue($response->result);

        $responseData = $response->data;
        $createdAt = ArrayHelper::remove($responseData, 'created_at');
        $updatedAt = ArrayHelper::remove($responseData, 'updated_at');

        $this->assertLessThanOrEqual($time, $createdAt);
        $this->assertLessThanOrEqual($time, $updatedAt);

        $this->assertEquals([
            'id' => 2,
            'name' => 'category-new',
            'slug' => 'category-new',
            'description' => 'desc-new',
            'visible' => 1,
            'sort' => 10,
            'author_id' => 1,
        ], $responseData);

        $category = CategoryRepo::findOne(['name' => 'category-new']);
        $this->assertEquals(array_merge($data, [
            'slug' => 'category-new',
            'author_id' => 1,
        ]), [
            'name' => $category->name,
            'description' => $category->description,
            'visible' => $category->visible,
            'sort' => $category->sort,
            'slug' => $category->slug,
            'author_id' => $category->author_id,
        ]);

        $this->assertArrayHasKey(CategoryForm::EVENT_BEFORE_CREATING, $this->eventsRaised);
        $this->assertArrayHasKey(CategoryForm::EVENT_AFTER_CREATING, $this->eventsRaised);
    }

    public function testCreateWithSlug(): void
    {
        $data = [
            'name' => 'category-new-with-slug',
            'slug' => 'cat-slug',
            'visible' => 1,
            'sort' => 10,
        ];
        $this->assertTrue($this->podium()->category->create($data, Member::findOne(1))->result);

        $category = CategoryRepo::findOne(['name' => 'category-new-with-slug']);
        $this->assertEquals($data, [
            'name' => $category->name,
            'visible' => $category->visible,
            'sort' => $category->sort,
            'slug' => $category->slug,
        ]);
    }

    public function testCreateEventPreventing(): void
    {
        $handler = static function ($event) {
            $event->canCreate = false;
        };
        Event::on(CategoryForm::class, CategoryForm::EVENT_BEFORE_CREATING, $handler);

        $data = [
            'name' => 'category-new',
            'visible' => 1,
            'sort' => 10,
        ];
        $this->assertFalse($this->podium()->category->create($data, Member::findOne(1))->result);

        $this->assertEmpty(CategoryRepo::findOne(['name' => 'category-new']));

        Event::off(CategoryForm::class, CategoryForm::EVENT_BEFORE_CREATING, $handler);
    }

    public function testCreateLoadFalse(): void
    {
        $this->assertFalse($this->podium()->category->create([], Member::findOne(1))->result);
    }

    public function testFailedCreate(): void
    {
        $this->assertFalse((new CategoryForm())->create()->result);
    }

    /**
     * @throws InsufficientDataException
     * @throws ModelNotFoundException
     */
    public function testUpdate(): void
    {
        Event::on(CategoryForm::class, CategoryForm::EVENT_BEFORE_EDITING, function () {
            $this->eventsRaised[CategoryForm::EVENT_BEFORE_EDITING] = true;
        });
        Event::on(CategoryForm::class, CategoryForm::EVENT_AFTER_EDITING, function () {
            $this->eventsRaised[CategoryForm::EVENT_AFTER_EDITING] = true;
        });

        $data = [
            'id' => 1,
            'name' => 'category-updated',
            'visible' => 0,
            'sort' => 2,
        ];

        $response = $this->podium()->category->edit($data);
        $time = time();

        $this->assertTrue($response->result);

        $responseData = $response->data;
        $updatedAt = ArrayHelper::remove($responseData, 'updated_at');

        $this->assertLessThanOrEqual($time, $updatedAt);

        $this->assertEquals([
            'id' => 1,
            'name' => 'category-updated',
            'slug' => 'category1',
            'description' => null,
            'visible' => 0,
            'sort' => 2,
            'author_id' => 1,
            'created_at' => 1,
            'archived' => 0,
        ], $responseData);

        $category = CategoryRepo::findOne(['name' => 'category-updated']);
        $this->assertEquals(array_merge($data, [
            'slug' => 'category1',
            'author_id' => 1,
        ]), [
            'id' => $category->id,
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

    /**
     * @throws InsufficientDataException
     * @throws ModelNotFoundException
     */
    public function testUpdateEventPreventing(): void
    {
        $handler = static function ($event) {
            $event->canEdit = false;
        };
        Event::on(CategoryForm::class, CategoryForm::EVENT_BEFORE_EDITING, $handler);

        $data = [
            'id' => 1,
            'name' => 'category-updated',
            'visible' => 0,
            'sort' => 2,
        ];
        $this->assertFalse($this->podium()->category->edit($data)->result);

        $this->assertNotEmpty(CategoryRepo::findOne(['name' => 'category1']));
        $this->assertEmpty(CategoryRepo::findOne(['name' => 'category-updated']));

        Event::off(CategoryForm::class, CategoryForm::EVENT_BEFORE_EDITING, $handler);
    }

    /**
     * @throws InsufficientDataException
     * @throws ModelNotFoundException
     */
    public function testUpdateLoadFalse(): void
    {
        $this->assertFalse($this->podium()->category->edit(['id' => 1])->result);
    }

    public function testFailedEdit(): void
    {
        $this->assertFalse((new CategoryForm())->edit()->result);
    }

    /**
     * @throws InsufficientDataException
     * @throws ModelNotFoundException
     */
    public function testUpdateNoId(): void
    {
        $this->expectException(InsufficientDataException::class);
        $this->podium()->category->edit([]);
    }

    /**
     * @throws InsufficientDataException
     * @throws ModelNotFoundException
     */
    public function testUpdateWrongId(): void
    {
        $this->expectException(ModelNotFoundException::class);
        $this->podium()->category->edit(['id' => 10000]);
    }

    /**
     * @runInSeparateProcess
     * Keep last in class
     */
    public function testAttributeLabels(): void
    {
        $this->assertEquals([
            'name' => 'category.name',
            'visible' => 'category.visible',
            'sort' => 'category.sort',
            'slug' => 'category.slug',
            'description' => 'category.description',
        ], (new CategoryForm())->attributeLabels());
    }
}
