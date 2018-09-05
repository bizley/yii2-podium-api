<?php

declare(strict_types=1);

namespace bizley\podium\tests\poll;

use bizley\podium\api\enums\MemberStatus;
use bizley\podium\api\models\poll\Poll;
use bizley\podium\tests\DbTestCase;
use yii\data\ActiveDataFilter;

/**
 * Class PollTest
 * @package bizley\podium\tests\poll
 */
class PollTest extends DbTestCase
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
                'sort' => 8,
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
                'post_id' => 2,
                'question' => 'question2',
                'created_at' => 1,
                'updated_at' => 1,
            ],
        ],
    ];

    public function testGetPollById(): void
    {
        $poll = Poll::findById(1);
        $this->assertEquals(1, $poll->getId());
    }

    public function testGetPollByPostId(): void
    {
        $poll = Poll::findByPostId(2);
        $this->assertEquals(2, $poll->getId());
    }

    public function testGetComponentPollByPostId(): void
    {
        $poll = $this->podium()->poll->getPollByPostId(2);
        $this->assertEquals(2, $poll->getId());
    }

    public function testNonExistingPoll(): void
    {
        $this->assertEmpty(Poll::findById(999));
    }

    public function testGetPollsByFilterEmpty(): void
    {
        $polls = Poll::findByFilter();
        $this->assertEquals(2, $polls->getTotalCount());
        $this->assertEquals([1, 2], $polls->getKeys());
    }

    public function testGetPollsByFilter(): void
    {
        $filter = new ActiveDataFilter([
            'searchModel' => function () {
                return (new \yii\base\DynamicModel(['id']))->addRule('id', 'integer');
            }
        ]);
        $filter->load(['filter' => ['id' => 2]], '');
        $polls = Poll::findByFilter($filter);
        $this->assertEquals(1, $polls->getTotalCount());
        $this->assertEquals([2], $polls->getKeys());
    }
}
