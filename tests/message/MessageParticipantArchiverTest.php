<?php

declare(strict_types=1);

namespace bizley\podium\tests\message;

use bizley\podium\api\enums\MemberStatus;
use bizley\podium\api\enums\MessageSide;
use bizley\podium\api\models\message\MessageParticipantArchiver;
use bizley\podium\api\repos\MessageParticipantRepo;
use bizley\podium\tests\DbTestCase;
use yii\base\Event;

/**
 * Class MessageParticipantArchiverTest
 * @package bizley\podium\tests\message
 */
class MessageParticipantArchiverTest extends DbTestCase
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
            [
                'id' => 2,
                'user_id' => '2',
                'username' => 'member2',
                'slug' => 'member2',
                'status_id' => MemberStatus::ACTIVE,
                'created_at' => 1,
                'updated_at' => 1,
            ],
        ],
        'podium_message' => [
            [
                'id' => 1,
                'subject' => 'subject1',
                'content' => 'content1',
                'created_at' => 1,
                'updated_at' => 1,
            ],
        ],
        'podium_message_participant' => [
            [
                'message_id' => 1,
                'member_id' => 1,
                'side_id' => MessageSide::SENDER,
                'created_at' => 1,
                'updated_at' => 1,
                'archived' => false,
            ],
            [
                'message_id' => 1,
                'member_id' => 2,
                'side_id' => MessageSide::RECEIVER,
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
        Event::on(MessageParticipantArchiver::class, MessageParticipantArchiver::EVENT_BEFORE_ARCHIVING, function () {
            static::$eventsRaised[MessageParticipantArchiver::EVENT_BEFORE_ARCHIVING] = true;
        });
        Event::on(MessageParticipantArchiver::class, MessageParticipantArchiver::EVENT_AFTER_ARCHIVING, function () {
            static::$eventsRaised[MessageParticipantArchiver::EVENT_AFTER_ARCHIVING] = true;
        });

        $this->assertTrue($this->podium()->message->archive(MessageParticipantArchiver::findOne([
            'message_id' => 1,
            'member_id' => 1,
        ]))->result);

        $this->assertEquals(true, MessageParticipantRepo::findOne([
            'message_id' => 1,
            'member_id' => 1,
        ])->archived);

        $this->assertArrayHasKey(MessageParticipantArchiver::EVENT_BEFORE_ARCHIVING, static::$eventsRaised);
        $this->assertArrayHasKey(MessageParticipantArchiver::EVENT_AFTER_ARCHIVING, static::$eventsRaised);
    }

    public function testArchiveEventPreventing(): void
    {
        $handler = function ($event) {
            $event->canArchive = false;
        };
        Event::on(MessageParticipantArchiver::class, MessageParticipantArchiver::EVENT_BEFORE_ARCHIVING, $handler);

        $this->assertFalse($this->podium()->message->archive(MessageParticipantArchiver::findOne([
            'message_id' => 1,
            'member_id' => 1,
        ]))->result);

        $this->assertEquals(false, MessageParticipantRepo::findOne([
            'message_id' => 1,
            'member_id' => 1,
        ])->archived);

        Event::off(MessageParticipantArchiver::class, MessageParticipantArchiver::EVENT_BEFORE_ARCHIVING, $handler);
    }

    public function testAlreadyArchived(): void
    {
        $this->assertFalse($this->podium()->message->archive(MessageParticipantArchiver::findOne([
            'message_id' => 1,
            'member_id' => 2,
        ]))->result);
    }

    public function testRevive(): void
    {
        Event::on(MessageParticipantArchiver::class, MessageParticipantArchiver::EVENT_BEFORE_REVIVING, function () {
            static::$eventsRaised[MessageParticipantArchiver::EVENT_BEFORE_REVIVING] = true;
        });
        Event::on(MessageParticipantArchiver::class, MessageParticipantArchiver::EVENT_AFTER_REVIVING, function () {
            static::$eventsRaised[MessageParticipantArchiver::EVENT_AFTER_REVIVING] = true;
        });

        $this->assertTrue($this->podium()->message->revive(MessageParticipantArchiver::findOne([
            'message_id' => 1,
            'member_id' => 2,
        ]))->result);

        $this->assertEquals(false, MessageParticipantRepo::findOne([
            'message_id' => 1,
            'member_id' => 1,
        ])->archived);

        $this->assertArrayHasKey(MessageParticipantArchiver::EVENT_BEFORE_REVIVING, static::$eventsRaised);
        $this->assertArrayHasKey(MessageParticipantArchiver::EVENT_AFTER_REVIVING, static::$eventsRaised);
    }

    public function testReviveEventPreventing(): void
    {
        $handler = function ($event) {
            $event->canRevive = false;
        };
        Event::on(MessageParticipantArchiver::class, MessageParticipantArchiver::EVENT_BEFORE_REVIVING, $handler);

        $this->assertFalse($this->podium()->message->revive(MessageParticipantArchiver::findOne([
            'message_id' => 1,
            'member_id' => 2,
        ]))->result);

        $this->assertEquals(true, MessageParticipantRepo::findOne([
            'message_id' => 1,
            'member_id' => 2,
        ])->archived);

        Event::off(MessageParticipantArchiver::class, MessageParticipantArchiver::EVENT_BEFORE_REVIVING, $handler);
    }

    public function testAlreadyRevived(): void
    {
        $this->assertFalse($this->podium()->message->revive(MessageParticipantArchiver::findOne([
            'message_id' => 1,
            'member_id' => 1,
        ]))->result);
    }
}
