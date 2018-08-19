<?php

declare(strict_types=1);

namespace bizley\podium\tests\group;

use bizley\podium\api\models\group\GroupForm;
use bizley\podium\api\repos\GroupRepo;
use bizley\podium\tests\DbTestCase;
use yii\base\Event;

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
        $this->assertTrue($this->podium()->group->create($data));

        $rank = GroupRepo::findOne(['name' => 'group-new']);
        $this->assertEquals($data, ['name' => $rank->name]);

        $this->assertArrayHasKey(GroupForm::EVENT_BEFORE_CREATING, $this->eventsRaised);
        $this->assertArrayHasKey(GroupForm::EVENT_AFTER_CREATING, $this->eventsRaised);
    }

    public function testCreateEventPreventing(): void
    {
        $handler = function ($event) {
            $event->canCreate = false;
        };
        Event::on(GroupForm::class, GroupForm::EVENT_BEFORE_CREATING, $handler);

        $data = ['name' => 'group-new'];
        $this->assertFalse($this->podium()->group->create($data));

        $this->assertEmpty(GroupRepo::findOne(['name' => 'group-new']));

        Event::off(GroupForm::class, GroupForm::EVENT_BEFORE_CREATING, $handler);
    }

    public function testCreateWithSameName(): void
    {
        $this->assertFalse($this->podium()->group->create(['name' => 'group1']));
    }

    public function testUpdate(): void
    {
        Event::on(GroupForm::class, GroupForm::EVENT_BEFORE_EDITING, function () {
            $this->eventsRaised[GroupForm::EVENT_BEFORE_EDITING] = true;
        });
        Event::on(GroupForm::class, GroupForm::EVENT_AFTER_EDITING, function () {
            $this->eventsRaised[GroupForm::EVENT_AFTER_EDITING] = true;
        });

        $data = ['name' => 'group-updated'];
        $this->assertTrue($this->podium()->group->edit(GroupForm::findOne(1),  $data));

        $rank = GroupRepo::findOne(['name' => 'group-updated']);
        $this->assertEquals($data, ['name' => $rank->name]);
        $this->assertEmpty(GroupRepo::findOne(['name' => 'group1']));

        $this->assertArrayHasKey(GroupForm::EVENT_BEFORE_EDITING, $this->eventsRaised);
        $this->assertArrayHasKey(GroupForm::EVENT_AFTER_EDITING, $this->eventsRaised);
    }

    public function testUpdateEventPreventing(): void
    {
        $handler = function ($event) {
            $event->canEdit = false;
        };
        Event::on(GroupForm::class, GroupForm::EVENT_BEFORE_EDITING, $handler);

        $data = ['name' => 'group-updated'];
        $this->assertFalse($this->podium()->group->edit(GroupForm::findOne(1),  $data));

        $this->assertNotEmpty(GroupRepo::findOne(['name' => 'group1']));
        $this->assertEmpty(GroupRepo::findOne(['name' => 'group-updated']));

        Event::off(GroupForm::class, GroupForm::EVENT_BEFORE_EDITING, $handler);
    }
}
