<?php

declare(strict_types=1);

namespace bizley\podium\tests\poll;

use bizley\podium\api\enums\MemberStatus;
use bizley\podium\api\enums\PollChoice;
use bizley\podium\api\enums\PostType;
use bizley\podium\api\models\poll\PollForm;
use bizley\podium\api\repos\PollRepo;
use bizley\podium\tests\DbTestCase;

/**
 * Class PollFormTest
 * @package bizley\podium\tests\poll
 */
class PollFormTest extends DbTestCase
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
        'podium_post' => [
            [
                'id' => 1,
                'category_id' => 1,
                'forum_id' => 1,
                'thread_id' => 1,
                'author_id' => 1,
                'content' => 'post1',
                'created_at' => 1,
                'updated_at' => 1,
                'type_id' => PostType::POLL,
            ],
        ],
        'podium_poll' => [
            [
                'id' => 1,
                'post_id' => 1,
                'question' => 'question1',
                'created_at' => 1,
                'updated_at' => 1,
            ],
            [
                'id' => 2,
                'post_id' => 1,
                'question' => 'question2',
                'created_at' => 1,
                'updated_at' => 1,
            ],
        ],
        'podium_poll_answer' => [
            [
                'id' => 1,
                'poll_id' => 2,
                'answer' => 'answer1',
                'created_at' => 1,
                'updated_at' => 1,
            ],
        ],
        'podium_poll_vote' => [
            [
                'poll_id' => 2,
                'member_id' => 1,
                'answer_id' => 1,
                'created_at' => 1,
            ],
        ],
    ];

    public function testCreate(): void
    {
        $poll = new PollForm([
            'post_id' => 1,
            'question' => 'question-new',
            'revealed' => false,
            'choice_id' => PollChoice::MULTIPLE,
            'expires_at' => 1,
        ]);

        $this->assertTrue($poll->create()->result);

        $pollCreated = PollRepo::findOne(['question' => 'question-new']);
        $this->assertEquals([
            'post_id' => 1,
            'revealed' => false,
            'choice_id' => PollChoice::MULTIPLE,
            'expires_at' => 1,
        ], [
            'post_id' => $pollCreated->post_id,
            'revealed' => $pollCreated->revealed,
            'choice_id' => $pollCreated->choice_id,
            'expires_at' => $pollCreated->expires_at,
        ]);
    }

    public function testUpdate(): void
    {
        $poll = PollForm::findOne(1);
        $poll->question = 'question-updated';

        $this->assertTrue($poll->edit()->result);

        $pollUpdated = PollRepo::findOne(1);
        $this->assertEquals('question-updated', $pollUpdated->question);
    }

    public function testUpdateAlreadyVoted(): void
    {
        $poll = PollForm::findOne(2);
        $poll->question = 'question-updated';

        $this->assertFalse($poll->edit()->result);
    }
}
