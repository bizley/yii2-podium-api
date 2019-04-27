<?php

declare(strict_types=1);

namespace bizley\podium\tests\poll;

use bizley\podium\api\enums\MemberStatus;
use bizley\podium\api\models\poll\PollVoteForm;
use bizley\podium\tests\DbTestCase;
use yii\base\NotSupportedException;

/**
 * Class PollVoteFormTest
 * @package bizley\podium\tests\poll
 */
class PollVoteFormTest extends DbTestCase
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
                'posts_count' => 3,
                'created_at' => 1,
                'updated_at' => 1,
            ],
        ],
        'podium_thread' => [
            [
                'id' => 1,
                'category_id' => 1,
                'forum_id' => 1,
                'author_id' => 1,
                'name' => 'thread1',
                'slug' => 'thread1',
                'posts_count' => 2,
                'created_at' => 1,
                'updated_at' => 1,
            ],
        ],
        'podium_poll' => [
            [
                'id' => 1,
                'thread_id' => 1,
                'author_id' => 1,
                'question' => 'question1',
                'created_at' => 1,
                'updated_at' => 1,
            ],
        ],
        'podium_poll_answer' => [
            [
                'id' => 1,
                'poll_id' => 1,
                'answer' => 'answer1',
                'created_at' => 1,
                'updated_at' => 1,
            ],
            [
                'id' => 2,
                'poll_id' => 1,
                'answer' => 'answer2',
                'created_at' => 1,
                'updated_at' => 1,
            ],
        ],
        'podium_poll_vote' => [
            [
                'poll_id' => 1,
                'member_id' => 1,
                'answer_id' => 2,
                'created_at' => 1,
            ],
        ],
    ];

    public function testCreate(): void
    {
        $pollVote = new PollVoteForm([
            'member_id' => 1,
            'poll_id' => 1,
            'answer_id' => 1,
        ]);

        $this->assertTrue($pollVote->create()->result);
    }

    /**
     * @throws NotSupportedException
     */
    public function testUpdate(): void
    {
        $this->expectException(NotSupportedException::class);
        PollVoteForm::findOne(1)->edit();
    }

    /**
     * @throws NotSupportedException
     */
    public function testLoadData(): void
    {
        $this->expectException(NotSupportedException::class);
        (new PollVoteForm())->loadData();
    }

    public function testFailedCreate(): void
    {
        $mock = $this->getMockBuilder(PollVoteForm::class)->setMethods(['save'])->getMock();
        $mock->method('save')->willReturn(false);

        $this->assertFalse($mock->create()->result);
    }
}
