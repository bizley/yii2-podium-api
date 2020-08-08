<?php

declare(strict_types=1);

namespace bizley\podium\tests\message;

use bizley\podium\api\components\ModelNotFoundException;
use bizley\podium\api\enums\MemberStatus;
use bizley\podium\api\enums\MessageSide;
use bizley\podium\api\models\member\Member;
use bizley\podium\api\models\message\MessageArchiver;
use bizley\podium\api\repos\MessageParticipantRepo;
use bizley\podium\tests\DbTestCase;
use yii\base\Event;

/**
 * Class MessageParticipantArchiverTest
 * @package bizley\podium\tests\message
 */
class MessageArchiverTest extends DbTestCase
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
    protected $eventsRaised = [];

    /**
     * @throws ModelNotFoundException
     */
    public function testArchive(): void
    {
        Event::on(MessageArchiver::class, MessageArchiver::EVENT_BEFORE_ARCHIVING, function () {
            $this->eventsRaised[MessageArchiver::EVENT_BEFORE_ARCHIVING] = true;
        });
        Event::on(MessageArchiver::class, MessageArchiver::EVENT_AFTER_ARCHIVING, function () {
            $this->eventsRaised[MessageArchiver::EVENT_AFTER_ARCHIVING] = true;
        });

        $this->assertTrue($this->podium()->message->archive(1, Member::findByUserId(1))->result);

        $this->assertEquals(true, MessageParticipantRepo::findOne([
            'message_id' => 1,
            'member_id' => 1,
        ])->archived);

        $this->assertArrayHasKey(MessageArchiver::EVENT_BEFORE_ARCHIVING, $this->eventsRaised);
        $this->assertArrayHasKey(MessageArchiver::EVENT_AFTER_ARCHIVING, $this->eventsRaised);
    }

    /**
     * @throws ModelNotFoundException
     */
    public function testArchiveEventPreventing(): void
    {
        $handler = static function ($event) {
            $event->canArchive = false;
        };
        Event::on(MessageArchiver::class, MessageArchiver::EVENT_BEFORE_ARCHIVING, $handler);

        $this->assertFalse($this->podium()->message->archive(1, Member::findByUserId(1))->result);

        $this->assertEquals(false, MessageParticipantRepo::findOne([
            'message_id' => 1,
            'member_id' => 1,
        ])->archived);

        Event::off(MessageArchiver::class, MessageArchiver::EVENT_BEFORE_ARCHIVING, $handler);
    }

    /**
     * @throws ModelNotFoundException
     */
    public function testAlreadyArchived(): void
    {
        $this->assertFalse($this->podium()->message->archive(1, Member::findByUserId(2))->result);
    }

    public function testFailedArchive(): void
    {
        $mock = $this->getMockBuilder(MessageArchiver::class)->setMethods(['save'])->getMock();
        $mock->method('save')->willReturn(false);

        $this->assertFalse($mock->archive()->result);
    }

    /**
     * @throws ModelNotFoundException
     */
    public function testNoMessageToArchive(): void
    {
        $this->expectException(ModelNotFoundException::class);
        $this->podium()->message->archive(999, Member::findByUserId(1));
    }

    /**
     * @throws ModelNotFoundException
     */
    public function testRevive(): void
    {
        Event::on(MessageArchiver::class, MessageArchiver::EVENT_BEFORE_REVIVING, function () {
            $this->eventsRaised[MessageArchiver::EVENT_BEFORE_REVIVING] = true;
        });
        Event::on(MessageArchiver::class, MessageArchiver::EVENT_AFTER_REVIVING, function () {
            $this->eventsRaised[MessageArchiver::EVENT_AFTER_REVIVING] = true;
        });

        $this->assertTrue($this->podium()->message->revive(1, Member::findByUserId(2))->result);

        $this->assertEquals(false, MessageParticipantRepo::findOne([
            'message_id' => 1,
            'member_id' => 1,
        ])->archived);

        $this->assertArrayHasKey(MessageArchiver::EVENT_BEFORE_REVIVING, $this->eventsRaised);
        $this->assertArrayHasKey(MessageArchiver::EVENT_AFTER_REVIVING, $this->eventsRaised);
    }

    /**
     * @throws ModelNotFoundException
     */
    public function testReviveEventPreventing(): void
    {
        $handler = static function ($event) {
            $event->canRevive = false;
        };
        Event::on(MessageArchiver::class, MessageArchiver::EVENT_BEFORE_REVIVING, $handler);

        $this->assertFalse($this->podium()->message->revive(1, Member::findByUserId(2))->result);

        $this->assertEquals(true, MessageParticipantRepo::findOne([
            'message_id' => 1,
            'member_id' => 2,
        ])->archived);

        Event::off(MessageArchiver::class, MessageArchiver::EVENT_BEFORE_REVIVING, $handler);
    }

    /**
     * @throws ModelNotFoundException
     */
    public function testAlreadyRevived(): void
    {
        $this->assertFalse($this->podium()->message->revive(1, Member::findByUserId(1))->result);
    }

    public function testFailedRevive(): void
    {
        $mock = $this->getMockBuilder(MessageArchiver::class)->setMethods(['save'])->getMock();
        $mock->method('save')->willReturn(false);

        $mock->archived = true;

        $this->assertFalse($mock->revive()->result);
    }

    /**
     * @throws ModelNotFoundException
     */
    public function testNoMessageToRevive(): void
    {
        $this->expectException(ModelNotFoundException::class);
        $this->podium()->message->revive(999, Member::findByUserId(1));
    }
}
