<?php

declare(strict_types=1);

namespace bizley\podium\tests\category;

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
    protected static $eventsRaised = [];

    public function testArchive(): void
    {
        Event::on(CategoryArchiver::class, CategoryArchiver::EVENT_BEFORE_ARCHIVING, function () {
            static::$eventsRaised[CategoryArchiver::EVENT_BEFORE_ARCHIVING] = true;
        });
        Event::on(CategoryArchiver::class, CategoryArchiver::EVENT_AFTER_ARCHIVING, function () {
            static::$eventsRaised[CategoryArchiver::EVENT_AFTER_ARCHIVING] = true;
        });

        $this->assertTrue($this->podium()->category->archive(CategoryArchiver::findOne(1))->result);

        $this->assertEquals(true, CategoryRepo::findOne(1)->archived);

        $this->assertArrayHasKey(CategoryArchiver::EVENT_BEFORE_ARCHIVING, static::$eventsRaised);
        $this->assertArrayHasKey(CategoryArchiver::EVENT_AFTER_ARCHIVING, static::$eventsRaised);
    }

    public function testArchiveEventPreventing(): void
    {
        $handler = function ($event) {
            $event->canArchive = false;
        };
        Event::on(CategoryArchiver::class, CategoryArchiver::EVENT_BEFORE_ARCHIVING, $handler);

        $this->assertFalse($this->podium()->category->archive(CategoryArchiver::findOne(1))->result);

        $this->assertEquals(false, CategoryRepo::findOne(1)->archived);

        Event::off(CategoryArchiver::class, CategoryArchiver::EVENT_BEFORE_ARCHIVING, $handler);
    }

    public function testAlreadyArchived(): void
    {
        $this->assertFalse($this->podium()->category->archive(CategoryArchiver::findOne(2))->result);
    }

    public function testRevive(): void
    {
        Event::on(CategoryArchiver::class, CategoryArchiver::EVENT_BEFORE_REVIVING, function () {
            static::$eventsRaised[CategoryArchiver::EVENT_BEFORE_REVIVING] = true;
        });
        Event::on(CategoryArchiver::class, CategoryArchiver::EVENT_AFTER_REVIVING, function () {
            static::$eventsRaised[CategoryArchiver::EVENT_AFTER_REVIVING] = true;
        });

        $this->assertTrue($this->podium()->category->revive(CategoryArchiver::findOne(2)));

        $this->assertEquals(false, CategoryRepo::findOne(2)->archived);

        $this->assertArrayHasKey(CategoryArchiver::EVENT_BEFORE_REVIVING, static::$eventsRaised);
        $this->assertArrayHasKey(CategoryArchiver::EVENT_AFTER_REVIVING, static::$eventsRaised);
    }

    public function testReviveEventPreventing(): void
    {
        $handler = function ($event) {
            $event->canRevive = false;
        };
        Event::on(CategoryArchiver::class, CategoryArchiver::EVENT_BEFORE_REVIVING, $handler);

        $this->assertFalse($this->podium()->category->revive(CategoryArchiver::findOne(2)));

        $this->assertEquals(true, CategoryRepo::findOne(2)->archived);

        Event::off(CategoryArchiver::class, CategoryArchiver::EVENT_BEFORE_REVIVING, $handler);
    }

    public function testAlreadyRevived(): void
    {
        $this->assertFalse($this->podium()->category->revive(CategoryArchiver::findOne(1)));
    }
}
