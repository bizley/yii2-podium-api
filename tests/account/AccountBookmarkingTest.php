<?php

declare(strict_types=1);

namespace bizley\podium\tests\account;

use bizley\podium\api\base\NoMembershipException;
use bizley\podium\api\enums\MemberStatus;
use bizley\podium\api\models\post\Post;
use bizley\podium\api\models\thread\Bookmarking;
use bizley\podium\api\repos\BookmarkRepo;
use bizley\podium\tests\AccountTestCase;
use bizley\podium\tests\props\UserIdentity;
use Yii;
use yii\base\Event;
use yii\db\Exception;

/**
 * Class AccountBookmarkingTest
 * @package bizley\podium\tests\account
 */
class AccountBookmarkingTest extends AccountTestCase
{
    /**
     * @var array
     */
    public $fixtures = [
        'podium_member' => [
            [
                'id' => 1,
                'user_id' => '1',
                'username' => 'member1',
                'slug' => 'member1',
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
                'created_at' => 100,
                'updated_at' => 100,
            ],
        ],
        'podium_bookmark' => [
            [
                'member_id' => 1,
                'thread_id' => 1,
                'last_seen' => 10,
                'updated_at' => 1,
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
    public function testMark(): void
    {
        Event::on(Bookmarking::class, Bookmarking::EVENT_BEFORE_MARKING, function () {
            $this->eventsRaised[Bookmarking::EVENT_BEFORE_MARKING] = true;
        });
        Event::on(Bookmarking::class, Bookmarking::EVENT_AFTER_MARKING, function () {
            $this->eventsRaised[Bookmarking::EVENT_AFTER_MARKING] = true;
        });

        $this->assertTrue($this->podium()->account->markPost(Post::findOne(2))->result);

        $this->assertEquals(100, BookmarkRepo::findOne([
            'member_id' => 1,
            'thread_id' => 1,
        ])->last_seen);

        $this->assertArrayHasKey(Bookmarking::EVENT_BEFORE_MARKING, $this->eventsRaised);
        $this->assertArrayHasKey(Bookmarking::EVENT_AFTER_MARKING, $this->eventsRaised);
    }

    /**
     * @throws NoMembershipException
     */
    public function testMarkEventPreventing(): void
    {
        $handler = static function ($event) {
            $event->canMark = false;
        };
        Event::on(Bookmarking::class, Bookmarking::EVENT_BEFORE_MARKING, $handler);

        $this->assertFalse($this->podium()->account->markPost(Post::findOne(2))->result);

        $this->assertEquals(10, BookmarkRepo::findOne([
            'member_id' => 1,
            'thread_id' => 1,
        ])->last_seen);

        Event::off(Bookmarking::class, Bookmarking::EVENT_BEFORE_MARKING, $handler);
    }

    /**
     * @throws NoMembershipException
     */
    public function testNoUpdateMark(): void
    {
        $this->assertTrue($this->podium()->account->markPost(Post::findOne(1))->result);

        $this->assertEquals(10, BookmarkRepo::findOne([
            'member_id' => 1,
            'thread_id' => 1,
        ])->last_seen);
    }
}
