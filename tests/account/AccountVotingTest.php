<?php

declare(strict_types=1);

namespace bizley\podium\tests\account;

use bizley\podium\api\base\NoMembershipException;
use bizley\podium\api\enums\MemberStatus;
use bizley\podium\api\enums\PollChoice;
use bizley\podium\api\enums\PostType;
use bizley\podium\api\models\poll\Poll;
use bizley\podium\api\models\poll\PollAnswer;
use bizley\podium\api\models\poll\PollVoter;
use bizley\podium\api\repos\PollVoteRepo;
use bizley\podium\tests\AccountTestCase;
use bizley\podium\tests\props\UserIdentity;
use Yii;
use yii\base\Event;
use yii\db\Exception;

/**
 * Class AccountVotingTest
 * @package bizley\podium\tests\account
 */
class AccountVotingTest extends AccountTestCase
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
            [
                'id' => 2,
                'category_id' => 1,
                'forum_id' => 1,
                'thread_id' => 1,
                'author_id' => 1,
                'content' => 'post2',
                'created_at' => 1,
                'updated_at' => 1,
                'type_id' => PostType::POLL,
            ],
            [
                'id' => 3,
                'category_id' => 1,
                'forum_id' => 1,
                'thread_id' => 1,
                'author_id' => 1,
                'content' => 'post3',
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
                'choice_id' => PollChoice::SINGLE,
                'created_at' => 1,
                'updated_at' => 1,
            ],
            [
                'id' => 2,
                'post_id' => 2,
                'question' => 'question2',
                'choice_id' => PollChoice::MULTIPLE,
                'created_at' => 1,
                'updated_at' => 1,
            ],
            [
                'id' => 3,
                'post_id' => 3,
                'question' => 'question3',
                'choice_id' => PollChoice::SINGLE,
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
                'poll_id' => 2,
                'answer' => 'answer21',
                'created_at' => 1,
                'updated_at' => 1,
            ],
            [
                'id' => 3,
                'poll_id' => 2,
                'answer' => 'answer22',
                'created_at' => 1,
                'updated_at' => 1,
            ],
            [
                'id' => 4,
                'poll_id' => 3,
                'answer' => 'answer3',
                'created_at' => 1,
                'updated_at' => 1,
            ],
        ],
        'podium_poll_vote' => [
            [
                'poll_id' => 3,
                'member_id' => 1,
                'answer_id' => 4,
                'created_at' => 1,
            ],
        ],
    ];

    /**
     * @var array
     */
    protected $eventsRaised = [];

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->fixturesUp();
        Yii::$app->user->setIdentity(new UserIdentity(['id' => '1']));
    }

    /**
     * @throws Exception
     */
    protected function tearDown(): void
    {
        $this->fixturesDown();
        parent::tearDown();
    }

    /**
     * @throws NoMembershipException
     */
    public function testVoteSingle(): void
    {
        Event::on(PollVoter::class, PollVoter::EVENT_BEFORE_VOTING, function () {
            $this->eventsRaised[PollVoter::EVENT_BEFORE_VOTING] = true;
        });
        Event::on(PollVoter::class, PollVoter::EVENT_AFTER_VOTING, function () {
            $this->eventsRaised[PollVoter::EVENT_AFTER_VOTING] = true;
        });

        $this->assertTrue($this->podium()->account->votePoll(Poll::findOne(1), [PollAnswer::findOne(1)])->result);

        $this->assertNotEmpty(PollVoteRepo::findOne([
            'member_id' => 1,
            'poll_id' => 1,
            'answer_id' => 1,
        ]));

        $this->assertArrayHasKey(PollVoter::EVENT_BEFORE_VOTING, $this->eventsRaised);
        $this->assertArrayHasKey(PollVoter::EVENT_AFTER_VOTING, $this->eventsRaised);
    }

    /**
     * @throws NoMembershipException
     */
    public function testVoteEventPreventing(): void
    {
        $handler = static function ($event) {
            $event->canVote = false;
        };
        Event::on(PollVoter::class, PollVoter::EVENT_BEFORE_VOTING, $handler);

        $this->assertFalse($this->podium()->account->votePoll(Poll::findOne(1), [PollAnswer::findOne(1)])->result);

        $this->assertEmpty(PollVoteRepo::findOne([
            'member_id' => 1,
            'poll_id' => 1,
            'answer_id' => 1,
        ]));

        Event::off(PollVoter::class, PollVoter::EVENT_BEFORE_VOTING, $handler);
    }

    /**
     * @throws NoMembershipException
     */
    public function testVoteAgain(): void
    {
        $this->assertFalse($this->podium()->account->votePoll(Poll::findOne(3), [PollAnswer::findOne(4)])->result);
    }

    /**
     * @throws NoMembershipException
     */
    public function testVoteMultiple(): void
    {
        $this->assertTrue($this->podium()->account->votePoll(Poll::findOne(2), PollAnswer::findAll(['poll_id' => 2]))->result);

        $this->assertNotEmpty(PollVoteRepo::findOne([
            'member_id' => 1,
            'poll_id' => 2,
            'answer_id' => 2,
        ]));
        $this->assertNotEmpty(PollVoteRepo::findOne([
            'member_id' => 1,
            'poll_id' => 2,
            'answer_id' => 3,
        ]));
    }

    /**
     * @throws NoMembershipException
     */
    public function testVoteMultipleInSingleChoicePoll(): void
    {
        $this->assertFalse($this->podium()->account->votePoll(Poll::findOne(1), PollAnswer::findAll(['poll_id' => 2]))->result);
    }

    /**
     * @throws NoMembershipException
     */
    public function testVoteWrongAnswer(): void
    {
        $this->assertFalse($this->podium()->account->votePoll(Poll::findOne(1), [PollAnswer::findOne(4)])->result);
    }
}
