<?php

declare(strict_types=1);

namespace bizley\podium\tests\forum;

use bizley\podium\api\base\InsufficientDataException;
use bizley\podium\api\base\ModelNotFoundException;
use bizley\podium\api\enums\MemberStatus;
use bizley\podium\api\models\category\Category;
use bizley\podium\api\models\forum\Forum;
use bizley\podium\api\models\forum\ForumForm;
use bizley\podium\api\models\member\Member;
use bizley\podium\api\models\thread\Thread;
use bizley\podium\api\repos\ForumRepo;
use bizley\podium\tests\DbTestCase;
use yii\base\Event;
use yii\base\NotSupportedException;
use yii\helpers\ArrayHelper;
use function array_merge;

/**
 * Class ForumFormTest
 * @package bizley\podium\tests\forum
 */
class ForumFormTest extends DbTestCase
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
        Event::on(ForumForm::class, ForumForm::EVENT_BEFORE_CREATING, function () {
            $this->eventsRaised[ForumForm::EVENT_BEFORE_CREATING] = true;
        });
        Event::on(ForumForm::class, ForumForm::EVENT_AFTER_CREATING, function () {
            $this->eventsRaised[ForumForm::EVENT_AFTER_CREATING] = true;
        });

        $data = [
            'name' => 'forum-new',
            'visible' => 1,
            'sort' => 10,
        ];

        $response = $this->podium()->forum->create($data, Member::findOne(1), Category::findOne(1));
        $time = time();

        $this->assertTrue($response->result);

        $responseData = $response->data;
        $createdAt = ArrayHelper::remove($responseData, 'created_at');
        $updatedAt = ArrayHelper::remove($responseData, 'updated_at');

        $this->assertLessThanOrEqual($time, $createdAt);
        $this->assertLessThanOrEqual($time, $updatedAt);

        $this->assertEquals([
            'id' => 2,
            'category_id' => 1,
            'name' => 'forum-new',
            'slug' => 'forum-new',
            'visible' => 1,
            'sort' => 10,
            'author_id' => 1,
        ], $responseData);

        $forum = ForumRepo::findOne(['name' => 'forum-new']);
        $this->assertEquals(array_merge($data, [
            'slug' => 'forum-new',
            'author_id' => 1,
            'category_id' => 1,
            'threads_count' => 0,
            'posts_count' => 0,
        ]), [
            'name' => $forum->name,
            'visible' => $forum->visible,
            'sort' => $forum->sort,
            'slug' => $forum->slug,
            'author_id' => $forum->author_id,
            'category_id' => $forum->category_id,
            'threads_count' => $forum->threads_count,
            'posts_count' => $forum->posts_count,
        ]);

        $this->assertArrayHasKey(ForumForm::EVENT_BEFORE_CREATING, $this->eventsRaised);
        $this->assertArrayHasKey(ForumForm::EVENT_AFTER_CREATING, $this->eventsRaised);
    }

    public function testCreateWithSlug(): void
    {
        $data = [
            'name' => 'forum-new-with-slug',
            'slug' => 'for-slug',
            'visible' => 1,
            'sort' => 10,
        ];
        $this->assertTrue($this->podium()->forum->create($data, Member::findOne(1), Category::findOne(1))->result);

        $forum = ForumRepo::findOne(['name' => 'forum-new-with-slug']);
        $this->assertEquals($data, [
            'name' => $forum->name,
            'slug' => $forum->slug,
            'visible' => $forum->visible,
            'sort' => $forum->sort,
        ]);
    }

    public function testCreateEventPreventing(): void
    {
        $handler = static function ($event) {
            $event->canCreate = false;
        };
        Event::on(ForumForm::class, ForumForm::EVENT_BEFORE_CREATING, $handler);

        $data = [
            'name' => 'forum-new',
            'visible' => 1,
            'sort' => 10,
        ];
        $this->assertFalse($this->podium()->forum->create($data, Member::findOne(1), Category::findOne(1))->result);

        $this->assertEmpty(ForumRepo::findOne(['name' => 'forum-new']));

        Event::off(ForumForm::class, ForumForm::EVENT_BEFORE_CREATING, $handler);
    }

    public function testCreateLoadFalse(): void
    {
        $this->assertFalse($this->podium()->forum->create([], Member::findOne(1), Category::findOne(1))->result);
    }

    public function testFailedCreate(): void
    {
        $this->assertFalse((new ForumForm())->create()->result);
    }

    /**
     * @throws InsufficientDataException
     * @throws ModelNotFoundException
     */
    public function testUpdate(): void
    {
        Event::on(ForumForm::class, ForumForm::EVENT_BEFORE_EDITING, function () {
            $this->eventsRaised[ForumForm::EVENT_BEFORE_EDITING] = true;
        });
        Event::on(ForumForm::class, ForumForm::EVENT_AFTER_EDITING, function () {
            $this->eventsRaised[ForumForm::EVENT_AFTER_EDITING] = true;
        });

        $data = [
            'id' => 1,
            'name' => 'forum-updated',
            'visible' => 0,
            'sort' => 2,
        ];

        $response = $this->podium()->forum->edit($data);
        $time = time();

        $this->assertTrue($response->result);

        $responseData = $response->data;
        $updatedAt = ArrayHelper::remove($responseData, 'updated_at');

        $this->assertLessThanOrEqual($time, $updatedAt);

        $this->assertEquals([
            'id' => 1,
            'category_id' => 1,
            'name' => 'forum-updated',
            'slug' => 'forum1',
            'visible' => 0,
            'sort' => 2,
            'author_id' => 1,
            'created_at' => 1,
            'archived' => 0,
            'description' => null,
            'threads_count' => 0,
            'posts_count' => 0,
        ], $responseData);

        $forum = ForumRepo::findOne(['name' => 'forum-updated']);
        $this->assertEquals(array_merge($data, [
            'slug' => 'forum1',
            'author_id' => 1,
            'category_id' => 1,
            'threads_count' => 0,
            'posts_count' => 0,
        ]), [
            'id' => $forum->id,
            'name' => $forum->name,
            'visible' => $forum->visible,
            'sort' => $forum->sort,
            'slug' => $forum->slug,
            'author_id' => $forum->author_id,
            'category_id' => $forum->category_id,
            'threads_count' => $forum->threads_count,
            'posts_count' => $forum->posts_count,
        ]);
        $this->assertEmpty(ForumRepo::findOne(['name' => 'forum1']));

        $this->assertArrayHasKey(ForumForm::EVENT_BEFORE_EDITING, $this->eventsRaised);
        $this->assertArrayHasKey(ForumForm::EVENT_AFTER_EDITING, $this->eventsRaised);
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
        Event::on(ForumForm::class, ForumForm::EVENT_BEFORE_EDITING, $handler);

        $data = [
            'id' => 1,
            'name' => 'forum-updated',
            'visible' => 0,
            'sort' => 2,
        ];
        $this->assertFalse($this->podium()->forum->edit($data)->result);

        $this->assertNotEmpty(ForumRepo::findOne(['name' => 'forum1']));
        $this->assertEmpty(ForumRepo::findOne(['name' => 'forum-updated']));

        Event::off(ForumForm::class, ForumForm::EVENT_BEFORE_EDITING, $handler);
    }

    /**
     * @throws InsufficientDataException
     * @throws ModelNotFoundException
     */
    public function testUpdateLoadFalse(): void
    {
        $this->assertFalse($this->podium()->forum->edit(['id' => 1])->result);
    }

    public function testFailedEdit(): void
    {
        $this->assertFalse((new ForumForm())->edit()->result);
    }

    /**
     * @throws InsufficientDataException
     * @throws ModelNotFoundException
     */
    public function testUpdateNoId(): void
    {
        $this->expectException(InsufficientDataException::class);
        $this->podium()->forum->edit([]);
    }

    /**
     * @throws InsufficientDataException
     * @throws ModelNotFoundException
     */
    public function testUpdateWrongId(): void
    {
        $this->expectException(ModelNotFoundException::class);
        $this->podium()->forum->edit(['id' => 10000]);
    }

    /**
     * @throws NotSupportedException
     */
    public function testSetForum(): void
    {
        $this->expectException(NotSupportedException::class);
        (new ForumForm())->setForum(new Forum());
    }

    /**
     * @throws NotSupportedException
     */
    public function testSetThread(): void
    {
        $this->expectException(NotSupportedException::class);
        (new ForumForm())->setThread(new Thread());
    }

    /**
     * @runInSeparateProcess
     * Keep last in class
     */
    public function testAttributeLabels(): void
    {
        $this->assertEquals([
            'name' => 'forum.name',
            'visible' => 'forum.visible',
            'sort' => 'forum.sort',
            'slug' => 'forum.slug',
        ], (new ForumForm())->attributeLabels());
    }
}
