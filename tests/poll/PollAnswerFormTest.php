<?php

declare(strict_types=1);

namespace bizley\podium\tests\poll;

use bizley\podium\api\enums\MemberStatus;
use bizley\podium\api\enums\PostType;
use bizley\podium\api\models\poll\PollAnswerForm;
use bizley\podium\api\repos\PollAnswerRepo;
use bizley\podium\tests\DbTestCase;
use yii\base\NotSupportedException;

/**
 * Class PollAnswerFormTest
 * @package bizley\podium\tests\poll
 */
class PollAnswerFormTest extends DbTestCase
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

    public function testCreate(): void
    {
        $pollAnswer = new PollAnswerForm([
            'poll_id' => 1,
            'answer' => 'answer-new',
        ]);

        $this->assertTrue($pollAnswer->create()->result);
        $this->assertEquals(1, PollAnswerRepo::findOne(['answer' => 'answer-new'])->poll_id);
    }

    /**
     * @throws NotSupportedException
     */
    public function testUpdate(): void
    {
        $this->expectException(NotSupportedException::class);
        PollAnswerForm::findOne(1)->edit();
    }

    /**
     * @throws NotSupportedException
     */
    public function testLoadData(): void
    {
        $this->expectException(NotSupportedException::class);
        (new PollAnswerForm())->loadData();
    }

    public function testFailedCreate(): void
    {
        $mock = $this->getMockBuilder(PollAnswerForm::class)->setMethods(['save'])->getMock();
        $mock->method('save')->willReturn(false);

        $this->assertFalse($mock->create()->result);
    }
}
