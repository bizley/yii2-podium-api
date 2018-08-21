<?php

declare(strict_types=1);

namespace bizley\podium\tests\post;

use bizley\podium\api\enums\MemberStatus;
use bizley\podium\api\enums\PollChoice;
use bizley\podium\api\enums\PostType;
use bizley\podium\api\models\member\Member;
use bizley\podium\api\models\poll\PostPollForm;
use bizley\podium\api\models\thread\Thread;
use bizley\podium\api\repos\ForumRepo;
use bizley\podium\api\repos\PollAnswerRepo;
use bizley\podium\api\repos\PollRepo;
use bizley\podium\api\repos\PostRepo;
use bizley\podium\api\repos\ThreadRepo;
use bizley\podium\tests\DbTestCase;
use yii\base\Event;

/**
 * Class PostPollFormTest
 * @package bizley\podium\tests\post
 */
class PostPollFormTest extends DbTestCase
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
            [
                'id' => 3,
                'poll_id' => 2,
                'answer' => 'answer3',
                'created_at' => 1,
                'updated_at' => 1,
            ],
        ],
        'podium_poll_vote' => [
            [
                'poll_id' => 2,
                'member_id' => 1,
                'answer_id' => 3,
                'created_at' => 1,
            ],
        ],
    ];

    /**
     * @var array
     */
    protected $eventsRaised = [];

    public function testCreate(): void
    {
        Event::on(PostPollForm::class, PostPollForm::EVENT_BEFORE_CREATING, function () {
            $this->eventsRaised[PostPollForm::EVENT_BEFORE_CREATING] = true;
        });
        Event::on(PostPollForm::class, PostPollForm::EVENT_AFTER_CREATING, function () {
            $this->eventsRaised[PostPollForm::EVENT_AFTER_CREATING] = true;
        });

        $data = [
            'type_id' => PostType::POLL,
            'content' => 'post-new',
            'question' => 'question-new',
            'revealed' => false,
            'choice_id' => PollChoice::MULTIPLE,
            'expires_at' => 1,
            'answers' => ['answer1', 'answer2'],
        ];
        $this->assertTrue($this->podium()->post->create($data, Member::findOne(1), Thread::findOne(1)));

        $post = PostRepo::findOne(['content' => 'post-new']);
        $this->assertEquals([
            'content' => 'post-new',
            'author_id' => 1,
            'category_id' => 1,
            'forum_id' => 1,
            'thread_id' => 1,
            'edited' => 0,
            'likes' => 0,
            'dislikes' => 0,
            'edited_at' => null,
            'type_id' => PostType::POLL,
        ], [
            'content' => $post->content,
            'author_id' => $post->author_id,
            'category_id' => $post->category_id,
            'forum_id' => $post->forum_id,
            'thread_id' => $post->thread_id,
            'edited' => $post->edited,
            'likes' => $post->likes,
            'dislikes' => $post->dislikes,
            'edited_at' => $post->edited_at,
            'type_id' => $post->type_id,
        ]);

        $poll = PollRepo::findOne(['post_id' => $post->id]);
        $this->assertEquals([
            'question' => 'question-new',
            'revealed' => false,
            'choice_id' => PollChoice::MULTIPLE,
            'expires_at' => 1,
        ], [
            'question' => $poll->question,
            'revealed' => $poll->revealed,
            'choice_id' => $poll->choice_id,
            'expires_at' => $poll->expires_at,
        ]);

        $answers = PollAnswerRepo::findAll(['poll_id' => $poll->id]);
        $this->assertCount(2, $answers);
        $this->assertEquals('answer1', $answers[0]->answer);
        $this->assertEquals('answer2', $answers[1]->answer);

        $this->assertEquals(3, ThreadRepo::findOne(1)->posts_count);
        $this->assertEquals(4, ForumRepo::findOne(1)->posts_count);

        $this->assertArrayHasKey(PostPollForm::EVENT_BEFORE_CREATING, $this->eventsRaised);
        $this->assertArrayHasKey(PostPollForm::EVENT_AFTER_CREATING, $this->eventsRaised);
    }

    public function testCreateEventPreventing(): void
    {
        $handler = function ($event) {
            $event->canCreate = false;
        };
        Event::on(PostPollForm::class, PostPollForm::EVENT_BEFORE_CREATING, $handler);

        $data = [
            'type_id' => PostType::POLL,
            'content' => 'post-new',
            'question' => 'question-new',
            'revealed' => false,
            'choice_id' => PollChoice::MULTIPLE,
            'expires_at' => 1,
            'answers' => ['answer1', 'answer2'],
        ];
        $this->assertFalse($this->podium()->post->create($data, Member::findOne(1), Thread::findOne(1)));

        $this->assertEmpty(PostRepo::findOne(['content' => 'post-new']));

        $this->assertEquals(2, ThreadRepo::findOne(1)->posts_count);
        $this->assertEquals(3, ForumRepo::findOne(1)->posts_count);

        Event::off(PostPollForm::class, PostPollForm::EVENT_BEFORE_CREATING, $handler);
    }

    public function testUpdate(): void
    {
        Event::on(PostPollForm::class, PostPollForm::EVENT_BEFORE_EDITING, function () {
            $this->eventsRaised[PostPollForm::EVENT_BEFORE_EDITING] = true;
        });
        Event::on(PostPollForm::class, PostPollForm::EVENT_AFTER_EDITING, function () {
            $this->eventsRaised[PostPollForm::EVENT_AFTER_EDITING] = true;
        });

        $data = [
            'content' => 'post-updated',
            'question' => 'question-updated',
            'revealed' => true,
            'choice_id' => PollChoice::SINGLE,
            'expires_at' => 2,
            'answers' => ['answer3'],
        ];
        $this->assertTrue($this->podium()->post->edit(PostPollForm::findOne(1),  $data));

        $post = PostRepo::findOne(['content' => 'post-updated']);
        $this->assertEquals([
            'author_id' => 1,
            'category_id' => 1,
            'forum_id' => 1,
            'thread_id' => 1,
            'edited' => 1,
            'likes' => 0,
            'dislikes' => 0,
            'type_id' => PostType::POLL,
        ], [
            'author_id' => $post->author_id,
            'category_id' => $post->category_id,
            'forum_id' => $post->forum_id,
            'thread_id' => $post->thread_id,
            'edited' => $post->edited,
            'likes' => $post->likes,
            'dislikes' => $post->dislikes,
            'type_id' => $post->type_id,
        ]);
        $this->assertEmpty(PostRepo::findOne(['content' => 'post1']));

        $poll = PollRepo::findOne(['post_id' => $post->id]);
        $this->assertEquals([
            'question' => 'question-updated',
            'revealed' => true,
            'choice_id' => PollChoice::SINGLE,
            'expires_at' => 2,
        ], [
            'question' => $poll->question,
            'revealed' => $poll->revealed,
            'choice_id' => $poll->choice_id,
            'expires_at' => $poll->expires_at,
        ]);

        $answers = PollAnswerRepo::findAll(['poll_id' => $poll->id]);
        $this->assertCount(1, $answers);
        $this->assertEquals('answer3', $answers[0]->answer);

        $this->assertEquals(2, ThreadRepo::findOne(1)->posts_count);
        $this->assertEquals(3, ForumRepo::findOne(1)->posts_count);

        $this->assertArrayHasKey(PostPollForm::EVENT_BEFORE_EDITING, $this->eventsRaised);
        $this->assertArrayHasKey(PostPollForm::EVENT_AFTER_EDITING, $this->eventsRaised);
    }

    public function testUpdateEventPreventing(): void
    {
        $handler = function ($event) {
            $event->canEdit = false;
        };
        Event::on(PostPollForm::class, PostPollForm::EVENT_BEFORE_EDITING, $handler);

        $data = [
            'content' => 'post-updated',
            'question' => 'question-updated',
            'revealed' => true,
            'choice_id' => PollChoice::SINGLE,
            'expires_at' => 2,
            'answers' => ['answer3'],
        ];
        $this->assertFalse($this->podium()->post->edit(PostPollForm::findOne(1),  $data));

        $this->assertNotEmpty(PostRepo::findOne(['content' => 'post1']));
        $this->assertEmpty(PostRepo::findOne(['content' => 'post-updated']));

        Event::off(PostPollForm::class, PostPollForm::EVENT_BEFORE_EDITING, $handler);
    }

    public function testUpdateAlreadyVoted(): void
    {
        $data = [
            'content' => 'post-updated',
            'question' => 'question-updated',
            'revealed' => true,
            'choice_id' => PollChoice::SINGLE,
            'expires_at' => 2,
            'answers' => ['answer3'],
        ];
        $this->assertFalse($this->podium()->post->edit(PostPollForm::findOne(2),  $data));
    }
}
