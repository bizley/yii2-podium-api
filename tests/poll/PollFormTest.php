<?php

declare(strict_types=1);

namespace bizley\podium\tests\post;

use bizley\podium\api\base\InsufficientDataException;
use bizley\podium\api\base\ModelNotFoundException;
use bizley\podium\api\enums\MemberStatus;
use bizley\podium\api\enums\PollChoice;
use bizley\podium\api\models\category\Category;
use bizley\podium\api\models\forum\Forum;
use bizley\podium\api\models\member\Member;
use bizley\podium\api\models\poll\PollForm;
use bizley\podium\api\models\thread\Thread;
use bizley\podium\api\repos\PollAnswerRepo;
use bizley\podium\api\repos\PollRepo;
use bizley\podium\tests\DbTestCase;
use yii\base\Event;
use yii\base\NotSupportedException;
use yii\helpers\ArrayHelper;
use function time;

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
        'podium_poll' => [
            [
                'id' => 1,
                'thread_id' => 1,
                'author_id' => 1,
                'question' => 'question1',
                'revealed' => false,
                'expires_at' => 10,
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

    /**
     * @var array
     */
    protected $eventsRaised = [];

    public function testCreate(): void
    {
        Event::on(PollForm::class, PollForm::EVENT_BEFORE_CREATING, function () {
            $this->eventsRaised[PollForm::EVENT_BEFORE_CREATING] = true;
        });
        Event::on(PollForm::class, PollForm::EVENT_AFTER_CREATING, function () {
            $this->eventsRaised[PollForm::EVENT_AFTER_CREATING] = true;
        });

        $expires = time() + 3600;
        $data = [
            'question' => 'question2',
            'expires_at' => $expires,
            'answers' => [
                'answer21',
                'answer22',
            ],
        ];

        $response = $this->podium()->poll->create($data, Member::findOne(1), Thread::findOne(1));
        $time = time();

        $this->assertTrue($response->result);

        $responseData = $response->data;

        $this->assertLessThanOrEqual($time, ArrayHelper::remove($responseData, 'created_at'));
        $this->assertLessThanOrEqual($time, ArrayHelper::remove($responseData, 'updated_at'));

        $this->assertEquals([
            'id' => 2,
            'thread_id' => 1,
            'author_id' => 1,
            'question' => 'question2',
            'revealed' => true,
            'choice_id' => PollChoice::SINGLE,
            'expires_at' => $expires,
        ], $responseData);

        $poll = PollRepo::findOne(2);
        $this->assertEquals([
            'thread_id' => 1,
            'author_id' => 1,
            'question' => 'question2',
            'revealed' => true,
            'choice_id' => PollChoice::SINGLE,
            'expires_at' => $expires,
        ], [
            'thread_id' => $poll->thread_id,
            'author_id' => $poll->author_id,
            'question' => $poll->question,
            'revealed' => $poll->revealed,
            'choice_id' => $poll->choice_id,
            'expires_at' => $poll->expires_at,
        ]);

        $pollAnswer1 = PollAnswerRepo::findOne(['answer' => 'answer21']);
        $this->assertEquals([
            'poll_id' => 2,
            'answer' => 'answer21',
        ], [
            'poll_id' => $pollAnswer1->poll_id,
            'answer' => $pollAnswer1->answer,
        ]);

        $pollAnswer2 = PollAnswerRepo::findOne(['answer' => 'answer22']);
        $this->assertEquals([
            'poll_id' => 2,
            'answer' => 'answer22',
        ], [
            'poll_id' => $pollAnswer2->poll_id,
            'answer' => $pollAnswer2->answer,
        ]);

        $this->assertArrayHasKey(PollForm::EVENT_BEFORE_CREATING, $this->eventsRaised);
        $this->assertArrayHasKey(PollForm::EVENT_AFTER_CREATING, $this->eventsRaised);
    }

    public function testCreateEventPreventing(): void
    {
        $handler = static function ($event) {
            $event->canCreate = false;
        };
        Event::on(PollForm::class, PollForm::EVENT_BEFORE_CREATING, $handler);

        $data = [
            'question' => 'question2',
            'expires_at' => time() + 3600,
            'answers' => [
                'answer21',
                'answer22',
            ],
        ];
        $this->assertFalse($this->podium()->poll->create($data, Member::findOne(1), Thread::findOne(1))->result);

        $this->assertEmpty(PollRepo::findOne(2));
        $this->assertEmpty(PollAnswerRepo::findOne(['answer' => 'answer21']));
        $this->assertEmpty(PollAnswerRepo::findOne(['answer' => 'answer22']));

        Event::off(PollForm::class, PollForm::EVENT_BEFORE_CREATING, $handler);
    }

    public function testCreateLoadFalse(): void
    {
        $this->assertFalse($this->podium()->poll->create([], Member::findOne(1), Thread::findOne(1))->result);
    }

    public function testFailedCreateValidate(): void
    {
        $mock = $this->getMockBuilder(PollForm::class)->setMethods(['validate'])->getMock();
        $mock->method('validate')->willReturn(false);

        $this->assertFalse($mock->create()->result);
    }

    public function testFailedCreate(): void
    {
        $mock = $this->getMockBuilder(PollForm::class)->setMethods(['save'])->getMock();
        $mock->method('save')->willReturn(false);

        $this->assertFalse($mock->create()->result);
    }

    /**
     * @throws InsufficientDataException
     * @throws ModelNotFoundException
     */
    public function testUpdate(): void
    {
        Event::on(PollForm::class, PollForm::EVENT_BEFORE_EDITING, function () {
            $this->eventsRaised[PollForm::EVENT_BEFORE_EDITING] = true;
        });
        Event::on(PollForm::class, PollForm::EVENT_AFTER_EDITING, function () {
            $this->eventsRaised[PollForm::EVENT_AFTER_EDITING] = true;
        });

        $expires = time() + 3600;
        $data = [
            'id' => 1,
            'question' => 'updated-question',
            'expires_at' => $expires,
            'answers' => [
                'answer1',
                'answer12',
            ],
        ];

        $response = $this->podium()->poll->edit($data);
        $time = time();

        $this->assertTrue($response->result);

        $responseData = $response->data;

        $this->assertLessThanOrEqual($time, ArrayHelper::remove($responseData, 'edited_at'));
        $this->assertLessThanOrEqual($time, ArrayHelper::remove($responseData, 'updated_at'));

        $this->assertEquals([
            'id' => 1,
            'thread_id' => 1,
            'author_id' => 1,
            'question' => 'updated-question',
            'revealed' => false,
            'choice_id' => PollChoice::SINGLE,
            'expires_at' => $expires,
            'created_at' => 1,
        ], $responseData);

        $poll = PollRepo::findOne(1);
        $this->assertEquals([
            'thread_id' => 1,
            'author_id' => 1,
            'question' => 'updated-question',
            'revealed' => false,
            'choice_id' => PollChoice::SINGLE,
            'expires_at' => $expires,
        ], [
            'thread_id' => $poll->thread_id,
            'author_id' => $poll->author_id,
            'question' => $poll->question,
            'revealed' => $poll->revealed,
            'choice_id' => $poll->choice_id,
            'expires_at' => $poll->expires_at,
        ]);
        $this->assertEmpty(PollRepo::findOne(['question' => 'question1']));

        $pollAnswer1 = PollAnswerRepo::findOne(['answer' => 'answer1']);
        $this->assertEquals([
            'poll_id' => 1,
            'answer' => 'answer1',
        ], [
            'poll_id' => $pollAnswer1->poll_id,
            'answer' => $pollAnswer1->answer,
        ]);

        $pollAnswer2 = PollAnswerRepo::findOne(['answer' => 'answer12']);
        $this->assertEquals([
            'poll_id' => 1,
            'answer' => 'answer12',
        ], [
            'poll_id' => $pollAnswer2->poll_id,
            'answer' => $pollAnswer2->answer,
        ]);

        $this->assertArrayHasKey(PollForm::EVENT_BEFORE_EDITING, $this->eventsRaised);
        $this->assertArrayHasKey(PollForm::EVENT_AFTER_EDITING, $this->eventsRaised);
    }

    /**
     * @throws InsufficientDataException
     * @throws ModelNotFoundException
     */
    public function testUpdateEventPreventing(): void
    {
        $handler = static function ($event) {
            $event->canEdit = false;
        };
        Event::on(PollForm::class, PollForm::EVENT_BEFORE_EDITING, $handler);

        $data = [
            'id' => 1,
            'question' => 'updated-question',
            'expires_at' => time() + 3600,
            'answers' => [
                'answer1',
                'answer12',
            ],
        ];
        $this->assertFalse($this->podium()->poll->edit($data)->result);

        $this->assertNotEmpty(PollRepo::findOne(['question' => 'question1']));
        $this->assertEmpty(PollRepo::findOne(['question' => 'updated-question']));

        Event::off(PollForm::class, PollForm::EVENT_BEFORE_EDITING, $handler);
    }

    /**
     * @throws InsufficientDataException
     * @throws ModelNotFoundException
     */
    public function testReplaceAnswers(): void
    {
        $expires = time() + 3600;
        $data = [
            'id' => 1,
            'question' => 'question1',
            'expires_at' => $expires,
            'choice_id' => PollChoice::MULTIPLE,
            'answers' => [
                'answer-new-1',
                'answer-new-2',
            ],
        ];

        $response = $this->podium()->poll->edit($data);
        $time = time();

        $this->assertTrue($response->result);

        $responseData = $response->data;

        $this->assertLessThanOrEqual($time, ArrayHelper::remove($responseData, 'edited_at'));
        $this->assertLessThanOrEqual($time, ArrayHelper::remove($responseData, 'updated_at'));

        $this->assertEquals([
            'id' => 1,
            'thread_id' => 1,
            'author_id' => 1,
            'question' => 'question1',
            'revealed' => false,
            'choice_id' => PollChoice::MULTIPLE,
            'expires_at' => $expires,
            'created_at' => 1,
        ], $responseData);

        $poll = PollRepo::findOne(1);
        $this->assertEquals([
            'thread_id' => 1,
            'author_id' => 1,
            'question' => 'question1',
            'revealed' => false,
            'choice_id' => PollChoice::MULTIPLE,
            'expires_at' => $expires,
        ], [
            'thread_id' => $poll->thread_id,
            'author_id' => $poll->author_id,
            'question' => $poll->question,
            'revealed' => $poll->revealed,
            'choice_id' => $poll->choice_id,
            'expires_at' => $poll->expires_at,
        ]);

        $this->assertNotEmpty(PollAnswerRepo::findOne(['answer' => 'answer-new-1']));
        $this->assertNotEmpty(PollAnswerRepo::findOne(['answer' => 'answer-new-2']));
        $this->assertEmpty(PollAnswerRepo::findOne(['answer' => 'answer1']));
    }

    /**
     * @throws InsufficientDataException
     * @throws ModelNotFoundException
     */
    public function testUpdateLoadFalse(): void
    {
        $this->assertFalse($this->podium()->poll->edit(['id' => 1])->result);
    }

    public function testFailedEdit(): void
    {
        $mock = $this->getMockBuilder(PollForm::class)->setMethods(['save'])->getMock();
        $mock->method('save')->willReturn(false);

        $this->assertFalse($mock->edit()->result);
    }

    /**
     * @throws InsufficientDataException
     * @throws ModelNotFoundException
     */
    public function testUpdateNoId(): void
    {
        $this->expectException(InsufficientDataException::class);
        $this->podium()->poll->edit([]);
    }

    /**
     * @throws InsufficientDataException
     * @throws ModelNotFoundException
     */
    public function testUpdateWrongId(): void
    {
        $this->expectException(ModelNotFoundException::class);
        $this->podium()->poll->edit(['id' => 10000]);
    }

    /**
     * @throws NotSupportedException
     */
    public function testSetCategory(): void
    {
        $this->expectException(NotSupportedException::class);
        (new PollForm())->setCategory(Category::findOne(1));
    }

    /**
     * @throws NotSupportedException
     */
    public function testSetForum(): void
    {
        $this->expectException(NotSupportedException::class);
        (new PollForm())->setForum(Forum::findOne(1));
    }

    /**
     * @runInSeparateProcess
     * Keep last in class
     */
    public function testAttributeLabels(): void
    {
        $this->assertEquals([
            'revealed' => 'poll.revealed',
            'choice_id' => 'poll.choice.type',
            'question' => 'poll.question',
            'expires_at' => 'poll.expires',
            'answers' => 'poll.answers',
        ], (new PollForm())->attributeLabels());
    }
}
