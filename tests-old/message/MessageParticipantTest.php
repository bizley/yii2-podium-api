<?php

declare(strict_types=1);

namespace bizley\podium\tests\message;

use bizley\podium\api\enums\MemberStatus;
use bizley\podium\api\enums\MessageSide;
use bizley\podium\api\models\message\MessageParticipant;
use bizley\podium\tests\DbTestCase;
use yii\base\DynamicModel;
use yii\base\NotSupportedException;
use yii\data\ActiveDataFilter;

/**
 * Class MessageParticipantTest
 * @package bizley\podium\tests\message
 */
class MessageParticipantTest extends DbTestCase
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

    public function testGetMessageParticipantById(): void
    {
        $messageParticipant = MessageParticipant::findOne(1);
        $this->assertEquals(1, $messageParticipant->getMemberId());
    }

    public function testNonExistingMessage(): void
    {
        $this->assertEmpty(MessageParticipant::findOne(999));
    }

    public function testGetMessagesByFilterEmpty(): void
    {
        $messageParticipants = MessageParticipant::findByFilter();
        $this->assertEquals(2, $messageParticipants->getTotalCount());
        $this->assertEquals([['message_id' => 1, 'member_id' => 1], ['message_id' => 1, 'member_id' => 2]], $messageParticipants->getKeys());
    }

    public function testGetMessagesByFilter(): void
    {
        $filter = new ActiveDataFilter([
            'searchModel' => static function () {
                return (new DynamicModel(['member_id']))->addRule('member_id', 'integer');
            }
        ]);
        $filter->load(['filter' => ['member_id' => 2]], '');

        $messageParticipants = MessageParticipant::findByFilter($filter);

        $this->assertEquals(1, $messageParticipants->getTotalCount());
        $this->assertEquals([['message_id' => 1, 'member_id' => 2]], $messageParticipants->getKeys());
    }

    /**
     * @throws NotSupportedException
     */
    public function testGetPostsCount(): void
    {
        $this->expectException(NotSupportedException::class);
        (new MessageParticipant())->getPostsCount();
    }

    public function testIsArchived(): void
    {
        $this->assertTrue(MessageParticipant::findOne([
            'message_id' => 1,
            'member_id' => 2,
        ])->isArchived());
        $this->assertFalse(MessageParticipant::findOne([
            'message_id' => 1,
            'member_id' => 1,
        ])->isArchived());
    }

    /**
     * @throws NotSupportedException
     */
    public function testGetId(): void
    {
        $this->expectException(NotSupportedException::class);
        (new MessageParticipant())->getId();
    }
}
