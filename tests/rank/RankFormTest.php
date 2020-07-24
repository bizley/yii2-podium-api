<?php

declare(strict_types=1);

namespace bizley\podium\tests\rank;

use bizley\podium\api\base\ModelNotFoundException;
use bizley\podium\api\InsufficientDataException;
use bizley\podium\api\models\rank\RankForm;
use bizley\podium\api\repos\RankRepo;
use bizley\podium\tests\DbTestCase;
use yii\base\Event;
use yii\helpers\ArrayHelper;

use function time;

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

        $response = $this->podium()->rank->create($data);
        $time = time();

        self::assertTrue($response->result);

        $responseData = $response->data;
        $createdAt = ArrayHelper::remove($responseData, 'created_at');
        $updatedAt = ArrayHelper::remove($responseData, 'updated_at');

        self::assertLessThanOrEqual($time, $createdAt);
        self::assertLessThanOrEqual($time, $updatedAt);

        self::assertEquals([
            'id' => 2,
            'name' => 'rank-new',
            'min_posts' => 99,
        ], $responseData);

        $rank = RankRepo::findOne(['name' => 'rank-new']);
        self::assertEquals($data, [
            'name' => $rank->name,
            'min_posts' => $rank->min_posts,
        ]);

        self::assertArrayHasKey(RankForm::EVENT_BEFORE_CREATING, $this->eventsRaised);
        self::assertArrayHasKey(RankForm::EVENT_AFTER_CREATING, $this->eventsRaised);
    }

    public function testCreateEventPreventing(): void
    {
        $handler = static function ($event) {
            $event->canCreate = false;
        };
        Event::on(RankForm::class, RankForm::EVENT_BEFORE_CREATING, $handler);

        $data = [
            'name' => 'rank-new',
            'min_posts' => 99,
        ];
        self::assertFalse($this->podium()->rank->create($data)->result);

        self::assertEmpty(RankRepo::findOne(['name' => 'rank-new']));

        Event::off(RankForm::class, RankForm::EVENT_BEFORE_CREATING, $handler);
    }

    public function testCreateLoadFalse(): void
    {
        self::assertFalse($this->podium()->rank->create([])->result);
    }

    public function testCreateWithSameMinPosts(): void
    {
        $data = [
            'name' => 'rank-new',
            'min_posts' => 0,
        ];
        self::assertFalse($this->podium()->rank->create($data)->result);
        self::assertEmpty(RankRepo::findOne(['name' => 'rank-new']));
    }

    /**
     * @throws InsufficientDataException
     * @throws ModelNotFoundException
     */
    public function testUpdate(): void
    {
        Event::on(RankForm::class, RankForm::EVENT_BEFORE_EDITING, function () {
            $this->eventsRaised[RankForm::EVENT_BEFORE_EDITING] = true;
        });
        Event::on(RankForm::class, RankForm::EVENT_AFTER_EDITING, function () {
            $this->eventsRaised[RankForm::EVENT_AFTER_EDITING] = true;
        });

        $data = [
            'id' => 1,
            'name' => 'rank-updated',
            'min_posts' => 52,
        ];

        $response = $this->podium()->rank->edit($data);
        $time = time();

        self::assertTrue($response->result);

        $responseData = $response->data;
        $updatedAt = ArrayHelper::remove($responseData, 'updated_at');

        self::assertLessThanOrEqual($time, $updatedAt);

        self::assertEquals([
            'id' => 1,
            'name' => 'rank-updated',
            'min_posts' => 52,
            'created_at' => 1,
        ], $responseData);

        $rank = RankRepo::findOne(['name' => 'rank-updated']);
        self::assertEquals($data, [
            'id' => $rank->id,
            'name' => $rank->name,
            'min_posts' => $rank->min_posts,
        ]);
        self::assertEmpty(RankRepo::findOne(['name' => 'rank1']));

        self::assertArrayHasKey(RankForm::EVENT_BEFORE_EDITING, $this->eventsRaised);
        self::assertArrayHasKey(RankForm::EVENT_AFTER_EDITING, $this->eventsRaised);
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
        Event::on(RankForm::class, RankForm::EVENT_BEFORE_EDITING, $handler);

        $data = [
            'id' => 1,
            'name' => 'rank-updated',
            'min_posts' => 52,
        ];
        self::assertFalse($this->podium()->rank->edit($data)->result);

        self::assertNotEmpty(RankRepo::findOne(['name' => 'rank1']));
        self::assertEmpty(RankRepo::findOne(['name' => 'rank-updated']));

        Event::off(RankForm::class, RankForm::EVENT_BEFORE_EDITING, $handler);
    }

    /**
     * @throws InsufficientDataException
     * @throws ModelNotFoundException
     */
    public function testUpdateLoadFalse(): void
    {
        self::assertFalse($this->podium()->rank->edit(['id' => 1])->result);
    }

    public function testFailedEdit(): void
    {
        $mock = $this->getMockBuilder(RankForm::class)->setMethods(['save'])->getMock();
        $mock->method('save')->willReturn(false);

        self::assertFalse($mock->edit()->result);
    }

    /**
     * @throws InsufficientDataException
     * @throws ModelNotFoundException
     */
    public function testUpdateNoId(): void
    {
        $this->expectException(InsufficientDataException::class);
        $this->podium()->rank->edit([]);
    }

    /**
     * @throws InsufficientDataException
     * @throws ModelNotFoundException
     */
    public function testUpdateWrongId(): void
    {
        $this->expectException(ModelNotFoundException::class);
        $this->podium()->rank->edit(['id' => 10000]);
    }
}
