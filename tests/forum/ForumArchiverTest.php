<?php

declare(strict_types=1);

namespace bizley\podium\tests\forum;

use bizley\podium\api\base\ModelNotFoundException;
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
    protected $eventsRaised = [];

    /**
     * @throws ModelNotFoundException
     */
    public function testArchive(): void
    {
        Event::on(ForumArchiver::class, ForumArchiver::EVENT_BEFORE_ARCHIVING, function () {
            $this->eventsRaised[ForumArchiver::EVENT_BEFORE_ARCHIVING] = true;
        });
        Event::on(ForumArchiver::class, ForumArchiver::EVENT_AFTER_ARCHIVING, function () {
            $this->eventsRaised[ForumArchiver::EVENT_AFTER_ARCHIVING] = true;
        });

        $this->assertTrue($this->podium()->forum->archive(1)->result);

        $this->assertEquals(true, ForumRepo::findOne(1)->archived);

        $this->assertArrayHasKey(ForumArchiver::EVENT_BEFORE_ARCHIVING, $this->eventsRaised);
        $this->assertArrayHasKey(ForumArchiver::EVENT_AFTER_ARCHIVING, $this->eventsRaised);
    }

    /**
     * @throws ModelNotFoundException
     */
    public function testArchiveEventPreventing(): void
    {
        $handler = static function ($event) {
            $event->canArchive = false;
        };
        Event::on(ForumArchiver::class, ForumArchiver::EVENT_BEFORE_ARCHIVING, $handler);

        $this->assertFalse($this->podium()->forum->archive(1)->result);

        $this->assertEquals(false, ForumRepo::findOne(1)->archived);

        Event::off(ForumArchiver::class, ForumArchiver::EVENT_BEFORE_ARCHIVING, $handler);
    }

    /**
     * @throws ModelNotFoundException
     */
    public function testAlreadyArchived(): void
    {
        $this->assertFalse($this->podium()->forum->archive(2)->result);
    }

    /**
     * @throws ModelNotFoundException
     */
    public function testNoForumToArchive(): void
    {
        $this->expectException(ModelNotFoundException::class);
        $this->podium()->forum->archive(999);
    }

    /**
     * @throws ModelNotFoundException
     */
    public function testRevive(): void
    {
        Event::on(ForumArchiver::class, ForumArchiver::EVENT_BEFORE_REVIVING, function () {
            $this->eventsRaised[ForumArchiver::EVENT_BEFORE_REVIVING] = true;
        });
        Event::on(ForumArchiver::class, ForumArchiver::EVENT_AFTER_REVIVING, function () {
            $this->eventsRaised[ForumArchiver::EVENT_AFTER_REVIVING] = true;
        });

        $this->assertTrue($this->podium()->forum->revive(2)->result);

        $this->assertEquals(false, ForumRepo::findOne(2)->archived);

        $this->assertArrayHasKey(ForumArchiver::EVENT_BEFORE_REVIVING, $this->eventsRaised);
        $this->assertArrayHasKey(ForumArchiver::EVENT_AFTER_REVIVING, $this->eventsRaised);
    }

    /**
     * @throws ModelNotFoundException
     */
    public function testReviveEventPreventing(): void
    {
        $handler = static function ($event) {
            $event->canRevive = false;
        };
        Event::on(ForumArchiver::class, ForumArchiver::EVENT_BEFORE_REVIVING, $handler);

        $this->assertFalse($this->podium()->forum->revive(2)->result);

        $this->assertEquals(true, ForumRepo::findOne(2)->archived);

        Event::off(ForumArchiver::class, ForumArchiver::EVENT_BEFORE_REVIVING, $handler);
    }

    /**
     * @throws ModelNotFoundException
     */
    public function testAlreadyRevived(): void
    {
        $this->assertFalse($this->podium()->forum->revive(1)->result);
    }

    /**
     * @throws ModelNotFoundException
     */
    public function testNoForumToRevive(): void
    {
        $this->expectException(ModelNotFoundException::class);
        $this->podium()->forum->revive(999);
    }
}
