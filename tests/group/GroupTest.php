<?php

declare(strict_types=1);

namespace bizley\podium\tests\group;

use bizley\podium\tests\DbTestCase;
use yii\data\ActiveDataFilter;

/**
 * Class GroupTest
 * @package bizley\podium\tests\group
 */
class GroupTest extends DbTestCase
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
            [
                'id' => 2,
                'name' => 'group2',
                'created_at' => 1,
                'updated_at' => 1,
            ],
        ],
    ];

    public function testGetGroupById(): void
    {
        $group = $this->podium()->group->getGroupById(1);
        $this->assertEquals(1, $group->getId());
    }

    public function testNonExistingGroup(): void
    {
        $this->assertEmpty($this->podium()->group->getGroupById(999));
    }

    public function testGetGroupsByFilterEmpty(): void
    {
        $groups = $this->podium()->group->getGroups();
        $this->assertEquals(2, $groups->getTotalCount());
        $this->assertEquals([1, 2], $groups->getKeys());
    }

    public function testGetGroupsByFilter(): void
    {
        $filter = new ActiveDataFilter([
            'searchModel' => function () {
                return (new \yii\base\DynamicModel(['id']))->addRule('id', 'integer');
            }
        ]);
        $filter->load(['filter' => ['id' => 2]], '');
        $groups = $this->podium()->group->getGroups($filter);
        $this->assertEquals(1, $groups->getTotalCount());
        $this->assertEquals([2], $groups->getKeys());
    }
}
