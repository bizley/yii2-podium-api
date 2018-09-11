<?php

declare(strict_types=1);

namespace bizley\podium\tests\message;

use bizley\podium\api\enums\MemberStatus;
use bizley\podium\api\models\message\Message;
use bizley\podium\tests\DbTestCase;
use yii\base\NotSupportedException;
use yii\data\ActiveDataFilter;

/**
 * Class MessageTest
 * @package bizley\podium\tests\message
 */
class MessageTest extends DbTestCase
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
                'reply_to_id' => 1,
                'subject' => 'subject2',
                'content' => 'content2',
                'created_at' => 1,
                'updated_at' => 1,
            ],
        ],
    ];

    public function testGetMessageById(): void
    {
        $message = $this->podium()->message->getMessageById(1);
        $this->assertEquals(1, $message->getId());
    }

    public function testNonExistingMessage(): void
    {
        $this->assertEmpty($this->podium()->message->getMessageById(999));
    }

    public function testGetMessagesByFilterEmpty(): void
    {
        $messages = $this->podium()->message->getMessages();
        $this->assertEquals(2, $messages->getTotalCount());
        $this->assertEquals([1, 2], $messages->getKeys());
    }

    public function testGetMessagesByFilter(): void
    {
        $filter = new ActiveDataFilter([
            'searchModel' => function () {
                return (new \yii\base\DynamicModel(['id']))->addRule('id', 'integer');
            }
        ]);
        $filter->load(['filter' => ['id' => 2]], '');
        $messages = $this->podium()->message->getMessages($filter);
        $this->assertEquals(1, $messages->getTotalCount());
        $this->assertEquals([2], $messages->getKeys());
    }

    public function testGetPostsCount(): void
    {
        $this->expectException(NotSupportedException::class);
        (new Message())->getPostsCount();
    }

    public function testGetNoParent(): void
    {
        $message = $this->podium()->message->getMessageById(1);
        $this->assertEmpty($message->getParent());
    }

    public function testGetParent(): void
    {
        $message = $this->podium()->message->getMessageById(2);
        $reply = Message::findOne(1);

        $this->assertEquals($reply, $message->getParent());
    }

    public function testIsArchived(): void
    {
        $this->expectException(NotSupportedException::class);
        (new Message())->isArchived();
    }
}
