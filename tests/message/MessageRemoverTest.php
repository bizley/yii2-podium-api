<?php

declare(strict_types=1);

namespace bizley\podium\tests\message;

use bizley\podium\api\base\ModelNotFoundException;
use bizley\podium\api\enums\MemberStatus;
use bizley\podium\api\enums\MessageSide;
use bizley\podium\api\models\member\Member;
use bizley\podium\api\models\message\MessageRemover;
use bizley\podium\api\repos\MessageParticipantRepo;
use bizley\podium\api\repos\MessageRepo;
use bizley\podium\tests\DbTestCase;
use Exception;
use yii\base\Event;

/**
 * Class MessageParticipantRemoverTest
 * @package bizley\podium\tests\message
 */
class MessageRemoverTest extends DbTestCase
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
            [
                'id' => 2,
                'subject' => 'subject2',
                'content' => 'content2',
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
                'archived' => true,
            ],
            [
                'message_id' => 1,
                'member_id' => 2,
                'side_id' => MessageSide::RECEIVER,
                'created_at' => 1,
                'updated_at' => 1,
                'archived' => false,
            ],
            [
                'message_id' => 2,
                'member_id' => 1,
                'side_id' => MessageSide::SENDER,
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
    public function testRemove(): void
    {
        Event::on(MessageRemover::class, MessageRemover::EVENT_BEFORE_REMOVING, function () {
            $this->eventsRaised[MessageRemover::EVENT_BEFORE_REMOVING] = true;
        });
        Event::on(MessageRemover::class, MessageRemover::EVENT_AFTER_REMOVING, function () {
            $this->eventsRaised[MessageRemover::EVENT_AFTER_REMOVING] = true;
        });

        $this->assertTrue($this->podium()->message->remove(1, Member::findByUserId(1))->result);

        $this->assertEmpty(MessageParticipantRepo::findOne([
            'message_id' => 1,
            'member_id' => 1,
        ]));

        $this->assertNotEmpty(MessageParticipantRepo::findOne([
            'message_id' => 1,
            'member_id' => 2,
        ]));

        $this->assertNotEmpty(MessageRepo::findOne(1));

        $this->assertArrayHasKey(MessageRemover::EVENT_BEFORE_REMOVING, $this->eventsRaised);
        $this->assertArrayHasKey(MessageRemover::EVENT_AFTER_REMOVING, $this->eventsRaised);
    }

    /**
     * @throws ModelNotFoundException
     */
    public function testRemoveEventPreventing(): void
    {
        $handler = static function ($event) {
            $event->canRemove = false;
        };
        Event::on(MessageRemover::class, MessageRemover::EVENT_BEFORE_REMOVING, $handler);

        $this->assertFalse($this->podium()->message->remove(1, Member::findByUserId(1))->result);

        $this->assertNotEmpty(MessageParticipantRepo::findOne([
            'message_id' => 1,
            'member_id' => 1,
        ]));

        Event::off(MessageRemover::class, MessageRemover::EVENT_BEFORE_REMOVING, $handler);
    }

    /**
     * @throws ModelNotFoundException
     */
    public function testRemoveLastOne(): void
    {
        $this->assertTrue($this->podium()->message->remove(2, Member::findByUserId(1))->result);

        $this->assertEmpty(MessageParticipantRepo::findOne([
            'message_id' => 2,
            'member_id' => 1,
        ]));
        $this->assertEmpty(MessageRepo::findOne(2));
    }

    /**
     * @throws ModelNotFoundException
     */
    public function testRemoveNonArchived(): void
    {
        $this->assertFalse($this->podium()->message->remove(1, Member::findByUserId(2))->result);

        $this->assertNotEmpty(MessageParticipantRepo::findOne([
            'message_id' => 1,
            'member_id' => 2,
        ]));
    }

    public function testExceptionRemove(): void
    {
        $mock = $this->getMockBuilder(MessageRemover::class)->setMethods(['delete'])->getMock();
        $mock->method('delete')->will($this->throwException(new Exception()));

        $mock->archived = true;

        $this->assertFalse($mock->remove()->result);
    }

    public function testFailedRemove(): void
    {
        $mock = $this->getMockBuilder(MessageRemover::class)->setMethods(['delete'])->getMock();
        $mock->method('delete')->willReturn(false);

        $mock->archived = true;

        $this->assertFalse($mock->remove()->result);
    }
}
