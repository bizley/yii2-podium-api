<?php

declare(strict_types=1);

namespace bizley\podium\tests\poll;

use bizley\podium\api\base\ModelNotFoundException;
use bizley\podium\api\enums\MemberStatus;
use bizley\podium\api\models\poll\PollRemover;
use bizley\podium\api\repos\PollAnswerRepo;
use bizley\podium\api\repos\PollRepo;
use bizley\podium\api\repos\PollVoteRepo;
use bizley\podium\api\repos\PostRepo;
use bizley\podium\tests\DbTestCase;
use yii\base\Event;

/**
 * Class PollRemoverTest
 * @package bizley\podium\tests\poll
 */
class PollRemoverTest extends DbTestCase
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
        'podium_poll_vote' => [
            [
                'poll_id' => 1,
                'answer_id' => 1,
                'member_id' => 1,
                'created_at' => 1,
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
        Event::on(PollRemover::class, PollRemover::EVENT_BEFORE_REMOVING, function () {
            $this->eventsRaised[PollRemover::EVENT_BEFORE_REMOVING] = true;
        });
        Event::on(PollRemover::class, PollRemover::EVENT_AFTER_REMOVING, function () {
            $this->eventsRaised[PollRemover::EVENT_AFTER_REMOVING] = true;
        });

        $this->assertTrue($this->podium()->poll->remove(1)->result);

        $this->assertEmpty(PollRepo::findOne(1));
        $this->assertEmpty(PollAnswerRepo::findOne(1));
        $this->assertEmpty(PollVoteRepo::findOne(1));

        $this->assertArrayHasKey(PollRemover::EVENT_BEFORE_REMOVING, $this->eventsRaised);
        $this->assertArrayHasKey(PollRemover::EVENT_AFTER_REMOVING, $this->eventsRaised);
    }

    /**
     * @throws ModelNotFoundException
     */
    public function testRemoveEventPreventing(): void
    {
        $handler = static function ($event) {
            $event->canRemove = false;
        };
        Event::on(PollRemover::class, PollRemover::EVENT_BEFORE_REMOVING, $handler);

        $this->assertFalse($this->podium()->poll->remove(1)->result);

        $this->assertNotEmpty(PollRepo::findOne(1));
        $this->assertNotEmpty(PollAnswerRepo::findOne(1));
        $this->assertNotEmpty(PollVoteRepo::findOne(1));

        Event::off(PollRemover::class, PollRemover::EVENT_BEFORE_REMOVING, $handler);
    }

    public function testExceptionRemove(): void
    {
        $mock = $this->getMockBuilder(PollRemover::class)->setMethods(['delete'])->getMock();
        $mock->method('delete')->will($this->throwException(new \Exception()));

        $this->assertFalse($mock->remove()->result);
    }

    public function testFailedRemove(): void
    {
        $mock = $this->getMockBuilder(PollRemover::class)->setMethods(['delete'])->getMock();
        $mock->method('delete')->willReturn(false);

        $this->assertFalse($mock->remove()->result);
    }
}
