<?php

declare(strict_types=1);

namespace bizley\podium\tests\group;

use bizley\podium\api\components\ModelNotFoundException;
use bizley\podium\api\InsufficientDataException;
use bizley\podium\api\models\group\GroupForm;
use bizley\podium\api\repos\GroupRepo;
use bizley\podium\tests\DbTestCase;
use yii\base\Event;
use yii\helpers\ArrayHelper;

use function time;

/**
 * Class GroupFormTest
 * @package bizley\podium\tests\group
 */
class GroupFormTest extends DbTestCase
{
    /**
     * @var array
     */
    public $fixtures = [
        'podium_group' => [
            [
                'id' => 1,
                'name' => 'group1',
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
        Event::on(GroupForm::class, GroupForm::EVENT_BEFORE_CREATING, function () {
            $this->eventsRaised[GroupForm::EVENT_BEFORE_CREATING] = true;
        });
        Event::on(GroupForm::class, GroupForm::EVENT_AFTER_CREATING, function () {
            $this->eventsRaised[GroupForm::EVENT_AFTER_CREATING] = true;
        });

        $data = ['name' => 'group-new'];

        $response = $this->podium()->group->create($data);
        $time = time();

        self::assertTrue($response->result);

        $responseData = $response->data;
        $createdAt = ArrayHelper::remove($responseData, 'created_at');
        $updatedAt = ArrayHelper::remove($responseData, 'updated_at');

        self::assertLessThanOrEqual($time, $createdAt);
        self::assertLessThanOrEqual($time, $updatedAt);

        self::assertEquals([
            'id' => 2,
            'name' => 'group-new',
        ], $responseData);

        $rank = GroupRepo::findOne(['name' => 'group-new']);
        self::assertEquals($data, ['name' => $rank->name]);

        self::assertArrayHasKey(GroupForm::EVENT_BEFORE_CREATING, $this->eventsRaised);
        self::assertArrayHasKey(GroupForm::EVENT_AFTER_CREATING, $this->eventsRaised);
    }

    public function testCreateEventPreventing(): void
    {
        $handler = static function ($event) {
            $event->canCreate = false;
        };
        Event::on(GroupForm::class, GroupForm::EVENT_BEFORE_CREATING, $handler);

        $data = ['name' => 'group-new'];
        self::assertFalse($this->podium()->group->create($data)->result);

        self::assertEmpty(GroupRepo::findOne(['name' => 'group-new']));

        Event::off(GroupForm::class, GroupForm::EVENT_BEFORE_CREATING, $handler);
    }

    public function testCreateLoadFalse(): void
    {
        self::assertFalse($this->podium()->group->create([])->result);
    }

    public function testCreateWithSameName(): void
    {
        self::assertFalse($this->podium()->group->create(['name' => 'group1'])->result);
    }

    /**
     * @throws InsufficientDataException
     * @throws ModelNotFoundException
     */
    public function testUpdate(): void
    {
        Event::on(GroupForm::class, GroupForm::EVENT_BEFORE_EDITING, function () {
            $this->eventsRaised[GroupForm::EVENT_BEFORE_EDITING] = true;
        });
        Event::on(GroupForm::class, GroupForm::EVENT_AFTER_EDITING, function () {
            $this->eventsRaised[GroupForm::EVENT_AFTER_EDITING] = true;
        });

        $data = [
            'id' => 1,
            'name' => 'group-updated',
        ];

        $response = $this->podium()->group->edit($data);
        $time = time();

        self::assertTrue($response->result);

        $responseData = $response->data;
        $updatedAt = ArrayHelper::remove($responseData, 'updated_at');

        self::assertLessThanOrEqual($time, $updatedAt);

        self::assertEquals([
            'id' => 1,
            'name' => 'group-updated',
            'created_at' => 1,
        ], $responseData);

        $rank = GroupRepo::findOne(['name' => 'group-updated']);
        self::assertEquals($data, [
            'id' => $rank->id,
            'name' => $rank->name,
        ]);
        self::assertEmpty(GroupRepo::findOne(['name' => 'group1']));

        self::assertArrayHasKey(GroupForm::EVENT_BEFORE_EDITING, $this->eventsRaised);
        self::assertArrayHasKey(GroupForm::EVENT_AFTER_EDITING, $this->eventsRaised);
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
        Event::on(GroupForm::class, GroupForm::EVENT_BEFORE_EDITING, $handler);

        $data = [
            'id' => 1,
            'name' => 'group-updated',
        ];
        self::assertFalse($this->podium()->group->edit($data)->result);

        self::assertNotEmpty(GroupRepo::findOne(['name' => 'group1']));
        self::assertEmpty(GroupRepo::findOne(['name' => 'group-updated']));

        Event::off(GroupForm::class, GroupForm::EVENT_BEFORE_EDITING, $handler);
    }

    /**
     * @throws InsufficientDataException
     * @throws ModelNotFoundException
     */
    public function testUpdateLoadFalse(): void
    {
        self::assertFalse($this->podium()->group->edit(['id' => 1])->result);
    }

    public function testFailedEdit(): void
    {
        self::assertFalse((new GroupForm())->edit()->result);
    }

    /**
     * @throws InsufficientDataException
     * @throws ModelNotFoundException
     */
    public function testUpdateNoId(): void
    {
        $this->expectException(InsufficientDataException::class);
        $this->podium()->group->edit([]);
    }

    /**
     * @throws InsufficientDataException
     * @throws ModelNotFoundException
     */
    public function testUpdateWrongId(): void
    {
        $this->expectException(ModelNotFoundException::class);
        $this->podium()->group->edit(['id' => 10000]);
    }
}
