<?php

declare(strict_types=1);

namespace bizley\podium\tests\forum;

use bizley\podium\api\enums\MemberStatus;
use bizley\podium\api\models\forum\ForumArchiver;
use bizley\podium\api\repos\ForumRepo;
use bizley\podium\tests\DbTestCase;
use yii\base\Event;

/**
 * Class ForumArchiverTest
 * @package bizley\podium\tests\forum
 */
class ForumArchiverTest extends DbTestCase
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
        'podium_forum' => [
            [
                'id' => 1,
                'category_id' => 1,
                'author_id' => 1,
                'name' => 'forum1',
                'slug' => 'forum1',
                'created_at' => 1,
                'updated_at' => 1,
                'archived' => false,
            ],
            [
                'id' => 2,
                'category_id' => 1,
                'author_id' => 1,
                'name' => 'forum2',
                'slug' => 'forum2',
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
        Event::on(ForumArchiver::class, ForumArchiver::EVENT_BEFORE_ARCHIVING, function () {
            static::$eventsRaised[ForumArchiver::EVENT_BEFORE_ARCHIVING] = true;
        });
        Event::on(ForumArchiver::class, ForumArchiver::EVENT_AFTER_ARCHIVING, function () {
            static::$eventsRaised[ForumArchiver::EVENT_AFTER_ARCHIVING] = true;
        });

        $this->assertTrue($this->podium()->forum->archive(ForumArchiver::findOne(1)));

        $this->assertEquals(true, ForumRepo::findOne(1)->archived);

        $this->assertArrayHasKey(ForumArchiver::EVENT_BEFORE_ARCHIVING, static::$eventsRaised);
        $this->assertArrayHasKey(ForumArchiver::EVENT_AFTER_ARCHIVING, static::$eventsRaised);
    }

    public function testArchiveEventPreventing(): void
    {
        $handler = function ($event) {
            $event->canArchive = false;
        };
        Event::on(ForumArchiver::class, ForumArchiver::EVENT_BEFORE_ARCHIVING, $handler);

        $this->assertFalse($this->podium()->forum->archive(ForumArchiver::findOne(1)));

        $this->assertEquals(false, ForumRepo::findOne(1)->archived);

        Event::off(ForumArchiver::class, ForumArchiver::EVENT_BEFORE_ARCHIVING, $handler);
    }

    public function testAlreadyArchived(): void
    {
        $this->assertFalse($this->podium()->forum->archive(ForumArchiver::findOne(2)));
    }

    public function testRevive(): void
    {
        Event::on(ForumArchiver::class, ForumArchiver::EVENT_BEFORE_REVIVING, function () {
            static::$eventsRaised[ForumArchiver::EVENT_BEFORE_REVIVING] = true;
        });
        Event::on(ForumArchiver::class, ForumArchiver::EVENT_AFTER_REVIVING, function () {
            static::$eventsRaised[ForumArchiver::EVENT_AFTER_REVIVING] = true;
        });

        $this->assertTrue($this->podium()->forum->revive(ForumArchiver::findOne(2)));

        $this->assertEquals(false, ForumRepo::findOne(2)->archived);

        $this->assertArrayHasKey(ForumArchiver::EVENT_BEFORE_REVIVING, static::$eventsRaised);
        $this->assertArrayHasKey(ForumArchiver::EVENT_AFTER_REVIVING, static::$eventsRaised);
    }

    public function testReviveEventPreventing(): void
    {
        $handler = function ($event) {
            $event->canRevive = false;
        };
        Event::on(ForumArchiver::class, ForumArchiver::EVENT_BEFORE_REVIVING, $handler);

        $this->assertFalse($this->podium()->forum->revive(ForumArchiver::findOne(2)));

        $this->assertEquals(true, ForumRepo::findOne(2)->archived);

        Event::off(ForumArchiver::class, ForumArchiver::EVENT_BEFORE_REVIVING, $handler);
    }

    public function testAlreadyRevived(): void
    {
        $this->assertFalse($this->podium()->forum->revive(ForumArchiver::findOne(1)));
    }
}
