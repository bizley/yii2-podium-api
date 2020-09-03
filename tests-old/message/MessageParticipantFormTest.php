<?php

declare(strict_types=1);

namespace bizley\podium\tests\message;

use bizley\podium\api\enums\MemberStatus;
use bizley\podium\api\enums\MessageSide;
use bizley\podium\api\enums\MessageStatus;
use bizley\podium\api\models\message\MessageForm;
use bizley\podium\api\repos\MessageParticipantRepo;
use bizley\podium\tests\DbTestCase;

/**
 * Class MessageParticipantFormTest
 * @package bizley\podium\tests\message
 */
class MessageParticipantFormTest extends DbTestCase
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
            ],
        ],
    ];

    public function testCreate(): void
    {
        $messageParticipant = new MessageForm([
            'message_id' => 1,
            'member_id' => 2,
            'side_id' => MessageSide::RECEIVER,
            'status_id' => MessageStatus::READ,
        ]);

        $this->assertTrue($messageParticipant->create()->result);

        $messageParticipantCreated = MessageParticipantRepo::findOne([
            'member_id' => 2,
            'message_id' => 1,
        ]);

        $this->assertEquals(MessageSide::RECEIVER, $messageParticipantCreated->side_id);
        $this->assertEquals(MessageStatus::READ, $messageParticipantCreated->status_id);
    }

    public function testFailedCreate(): void
    {
        $mock = $this->getMockBuilder(MessageForm::class)->setMethods(['save'])->getMock();
        $mock->method('save')->willReturn(false);

        $this->assertFalse($mock->create()->result);
    }

    public function testMarkRead(): void
    {
        $messageParticipant = MessageForm::findOne([
            'message_id' => 1,
            'member_id' => 1,
        ]);

        $this->assertTrue($messageParticipant->markRead()->result);
        $this->assertEquals(MessageStatus::READ, $messageParticipant->status_id);
    }

    public function testFailedMarkRead(): void
    {
        $mock = $this->getMockBuilder(MessageForm::class)->setMethods(['save'])->getMock();
        $mock->method('save')->willReturn(false);

        $this->assertFalse($mock->markRead()->result);
    }

    public function testFailedMarkReplied(): void
    {
        $mock = $this->getMockBuilder(MessageForm::class)->setMethods(['save'])->getMock();
        $mock->method('save')->willReturn(false);

        $this->assertFalse($mock->markReplied()->result);
    }
}
