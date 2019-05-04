<?php

declare(strict_types=1);

namespace bizley\podium\tests\category;

use bizley\podium\api\base\ModelNotFoundException;
use bizley\podium\api\enums\MemberStatus;
use bizley\podium\api\models\category\CategoryArchiver;
use bizley\podium\api\repos\CategoryRepo;
use bizley\podium\tests\DbTestCase;
use yii\base\Event;

/**
 * Class CategoryArchiverTest
 * @package bizley\podium\tests\category
 */
class CategoryArchiverTest extends DbTestCase
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
                'archived' => false,
            ],
            [
                'id' => 2,
                'author_id' => 1,
                'name' => 'category2',
                'slug' => 'category2',
                'created_at' => 1,
                'updated_at' => 1,
                'archived' => true,
            ],
        ],
    ];

    /**
     * @var array
     */
    protected $eventsRaised = [];

    /**
     * @throws ModelNotFoundException
     */
    public function testArchive(): void
    {
        Event::on(CategoryArchiver::class, CategoryArchiver::EVENT_BEFORE_ARCHIVING, function () {
            $this->eventsRaised[CategoryArchiver::EVENT_BEFORE_ARCHIVING] = true;
        });
        Event::on(CategoryArchiver::class, CategoryArchiver::EVENT_AFTER_ARCHIVING, function () {
            $this->eventsRaised[CategoryArchiver::EVENT_AFTER_ARCHIVING] = true;
        });

        $this->assertTrue($this->podium()->category->archive(1)->result);

        $this->assertEquals(true, CategoryRepo::findOne(1)->archived);

        $this->assertArrayHasKey(CategoryArchiver::EVENT_BEFORE_ARCHIVING, $this->eventsRaised);
        $this->assertArrayHasKey(CategoryArchiver::EVENT_AFTER_ARCHIVING, $this->eventsRaised);
    }

    /**
     * @throws ModelNotFoundException
     */
    public function testArchiveEventPreventing(): void
    {
        $handler = static function ($event) {
            $event->canArchive = false;
        };
        Event::on(CategoryArchiver::class, CategoryArchiver::EVENT_BEFORE_ARCHIVING, $handler);

        $this->assertFalse($this->podium()->category->archive(1)->result);

        $this->assertEquals(false, CategoryRepo::findOne(1)->archived);

        Event::off(CategoryArchiver::class, CategoryArchiver::EVENT_BEFORE_ARCHIVING, $handler);
    }

    /**
     * @throws ModelNotFoundException
     */
    public function testAlreadyArchived(): void
    {
        $this->assertFalse($this->podium()->category->archive(2)->result);
    }

    /**
     * @throws ModelNotFoundException
     */
    public function testNoCategoryToArchive(): void
    {
        $this->expectException(ModelNotFoundException::class);
        $this->podium()->category->archive(999);
    }

    /**
     * @throws ModelNotFoundException
     */
    public function testRevive(): void
    {
        Event::on(CategoryArchiver::class, CategoryArchiver::EVENT_BEFORE_REVIVING, function () {
            $this->eventsRaised[CategoryArchiver::EVENT_BEFORE_REVIVING] = true;
        });
        Event::on(CategoryArchiver::class, CategoryArchiver::EVENT_AFTER_REVIVING, function () {
            $this->eventsRaised[CategoryArchiver::EVENT_AFTER_REVIVING] = true;
        });

        $this->assertTrue($this->podium()->category->revive(2)->result);

        $this->assertEquals(false, CategoryRepo::findOne(2)->archived);

        $this->assertArrayHasKey(CategoryArchiver::EVENT_BEFORE_REVIVING, $this->eventsRaised);
        $this->assertArrayHasKey(CategoryArchiver::EVENT_AFTER_REVIVING, $this->eventsRaised);
    }

    /**
     * @throws ModelNotFoundException
     */
    public function testReviveEventPreventing(): void
    {
        $handler = static function ($event) {
            $event->canRevive = false;
        };
        Event::on(CategoryArchiver::class, CategoryArchiver::EVENT_BEFORE_REVIVING, $handler);

        $this->assertFalse($this->podium()->category->revive(2)->result);

        $this->assertEquals(true, CategoryRepo::findOne(2)->archived);

        Event::off(CategoryArchiver::class, CategoryArchiver::EVENT_BEFORE_REVIVING, $handler);
    }

    /**
     * @throws ModelNotFoundException
     */
    public function testAlreadyRevived(): void
    {
        $this->assertFalse($this->podium()->category->revive(1)->result);
    }

    /**
     * @throws ModelNotFoundException
     */
    public function testNoCategoryToRevive(): void
    {
        $this->expectException(ModelNotFoundException::class);
        $this->podium()->category->revive(999);
    }
}
