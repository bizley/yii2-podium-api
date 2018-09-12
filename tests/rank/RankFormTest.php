<?php

declare(strict_types=1);

namespace bizley\podium\tests\rank;

use bizley\podium\api\models\rank\RankForm;
use bizley\podium\api\repos\RankRepo;
use bizley\podium\tests\DbTestCase;
use yii\base\Event;

/**
 * Class RankFormTest
 * @package bizley\podium\tests\rank
 */
class RankFormTest extends DbTestCase
{
    /**
     * @var array
     */
    public $fixtures = [
        'podium_rank' => [
            [
                'id' => 1,
                'name' => 'rank1',
                'min_posts' => 0,
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
        Event::on(RankForm::class, RankForm::EVENT_BEFORE_CREATING, function () {
            $this->eventsRaised[RankForm::EVENT_BEFORE_CREATING] = true;
        });
        Event::on(RankForm::class, RankForm::EVENT_AFTER_CREATING, function () {
            $this->eventsRaised[RankForm::EVENT_AFTER_CREATING] = true;
        });

        $data = [
            'name' => 'rank-new',
            'min_posts' => 99,
        ];
        $this->assertTrue($this->podium()->rank->create($data)->result);

        $rank = RankRepo::findOne(['name' => 'rank-new']);
        $this->assertEquals($data, [
            'name' => $rank->name,
            'min_posts' => $rank->min_posts,
        ]);

        $this->assertArrayHasKey(RankForm::EVENT_BEFORE_CREATING, $this->eventsRaised);
        $this->assertArrayHasKey(RankForm::EVENT_AFTER_CREATING, $this->eventsRaised);
    }

    public function testCreateEventPreventing(): void
    {
        $handler = function ($event) {
            $event->canCreate = false;
        };
        Event::on(RankForm::class, RankForm::EVENT_BEFORE_CREATING, $handler);

        $data = [
            'name' => 'rank-new',
            'min_posts' => 99,
        ];
        $this->assertFalse($this->podium()->rank->create($data)->result);

        $this->assertEmpty(RankRepo::findOne(['name' => 'rank-new']));

        Event::off(RankForm::class, RankForm::EVENT_BEFORE_CREATING, $handler);
    }

    public function testCreateLoadFalse(): void
    {
        $this->assertFalse($this->podium()->rank->create([])->result);
    }

    public function testCreateWithSameMinPosts(): void
    {
        $data = [
            'name' => 'rank-new',
            'min_posts' => 0,
        ];
        $this->assertFalse($this->podium()->rank->create($data)->result);
        $this->assertEmpty(RankRepo::findOne(['name' => 'rank-new']));
    }

    public function testUpdate(): void
    {
        Event::on(RankForm::class, RankForm::EVENT_BEFORE_EDITING, function () {
            $this->eventsRaised[RankForm::EVENT_BEFORE_EDITING] = true;
        });
        Event::on(RankForm::class, RankForm::EVENT_AFTER_EDITING, function () {
            $this->eventsRaised[RankForm::EVENT_AFTER_EDITING] = true;
        });

        $data = [
            'name' => 'rank-updated',
            'min_posts' => 52,
        ];
        $this->assertTrue($this->podium()->rank->edit(RankForm::findOne(1), $data)->result);

        $rank = RankRepo::findOne(['name' => 'rank-updated']);
        $this->assertEquals($data, [
            'name' => $rank->name,
            'min_posts' => $rank->min_posts,
        ]);
        $this->assertEmpty(RankRepo::findOne(['name' => 'rank1']));

        $this->assertArrayHasKey(RankForm::EVENT_BEFORE_EDITING, $this->eventsRaised);
        $this->assertArrayHasKey(RankForm::EVENT_AFTER_EDITING, $this->eventsRaised);
    }

    public function testUpdateEventPreventing(): void
    {
        $handler = function ($event) {
            $event->canEdit = false;
        };
        Event::on(RankForm::class, RankForm::EVENT_BEFORE_EDITING, $handler);

        $data = [
            'name' => 'rank-updated',
            'min_posts' => 52,
        ];
        $this->assertFalse($this->podium()->rank->edit(RankForm::findOne(1), $data)->result);

        $this->assertNotEmpty(RankRepo::findOne(['name' => 'rank1']));
        $this->assertEmpty(RankRepo::findOne(['name' => 'rank-updated']));

        Event::off(RankForm::class, RankForm::EVENT_BEFORE_EDITING, $handler);
    }

    public function testUpdateLoadFalse(): void
    {
        $this->assertFalse($this->podium()->rank->edit(RankForm::findOne(1), [])->result);
    }

    public function testFailedEdit(): void
    {
        $mock = $this->getMockBuilder(RankForm::class)->setMethods(['save'])->getMock();
        $mock->method('save')->willReturn(false);

        $this->assertFalse($mock->edit()->result);
    }
}
