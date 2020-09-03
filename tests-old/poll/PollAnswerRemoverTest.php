<?php

declare(strict_types=1);

namespace bizley\podium\tests\poll;

use bizley\podium\api\enums\MemberStatus;
use bizley\podium\api\models\poll\PollAnswerRemover;
use bizley\podium\api\repos\PollAnswerRepo;
use bizley\podium\tests\DbTestCase;
use Exception;

/**
 * Class PollAnswerRemoverTest
 * @package bizley\podium\tests\poll
 */
class PollAnswerRemoverTest extends DbTestCase
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
                'threads_count' => 8,
                'posts_count' => 39,
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
                'posts_count' => 16,
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
        ],
    ];

    public function testRemove(): void
    {
        $this->assertTrue(PollAnswerRemover::findOne(1)->remove()->result);
        $this->assertEmpty(PollAnswerRepo::findOne(1));
    }

    public function testExceptionRemove(): void
    {
        $mock = $this->getMockBuilder(PollAnswerRemover::class)->setMethods(['delete'])->getMock();
        $mock->method('delete')->will($this->throwException(new Exception()));

        $this->assertFalse($mock->remove()->result);
    }

    public function testFailedRemove(): void
    {
        $mock = $this->getMockBuilder(PollAnswerRemover::class)->setMethods(['delete'])->getMock();
        $mock->method('delete')->willReturn(false);

        $this->assertFalse($mock->remove()->result);
    }
}
