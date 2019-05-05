<?php

declare(strict_types=1);

namespace bizley\podium\tests\member;

use bizley\podium\api\enums\MemberStatus;
use bizley\podium\api\models\member\Member;
use bizley\podium\api\models\member\MemberRemover;
use bizley\podium\tests\DbTestCase;
use yii\base\DynamicModel;
use yii\base\InvalidArgumentException;
use yii\base\InvalidConfigException;
use yii\base\NotSupportedException;
use yii\data\ActiveDataFilter;

/**
 * Class MemberTest
 * @package bizley\podium\tests\member
 */
class MemberTest extends DbTestCase
{
    /**
     * @var array
     */
    public $fixtures = [
        'podium_member' => [
            [
                'id' => 2,
                'user_id' => '10',
                'username' => 'member2',
                'slug' => 'member2',
                'status_id' => MemberStatus::ACTIVE,
                'created_at' => 1,
                'updated_at' => 1,
            ],
            [
                'id' => 3,
                'user_id' => '11',
                'username' => 'member3',
                'slug' => 'member3',
                'status_id' => MemberStatus::ACTIVE,
                'created_at' => 1,
                'updated_at' => 1,
            ],
        ],
        'podium_category' => [
            [
                'id' => 1,
                'author_id' => 2,
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
                'author_id' => 2,
                'name' => 'forum1',
                'slug' => 'forum1',
                'threads_count' => 5,
                'posts_count' => 67,
                'created_at' => 1,
                'updated_at' => 1,
            ],
        ],
        'podium_thread' => [
            [
                'id' => 1,
                'category_id' => 1,
                'forum_id' => 1,
                'author_id' => 2,
                'name' => 'thread1',
                'slug' => 'thread1',
                'posts_count' => 21,
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
                'author_id' => 2,
                'content' => 'post1',
                'created_at' => 1,
                'updated_at' => 1,
            ],
            [
                'id' => 2,
                'category_id' => 1,
                'forum_id' => 1,
                'thread_id' => 1,
                'author_id' => 2,
                'content' => 'post2',
                'created_at' => 1,
                'updated_at' => 1,
            ],
            [
                'id' => 3,
                'category_id' => 1,
                'forum_id' => 1,
                'thread_id' => 1,
                'author_id' => 2,
                'content' => 'post3',
                'created_at' => 1,
                'updated_at' => 1,
            ],
        ],
    ];

    public function testGetMemberById(): void
    {
        $member = $this->podium()->member->getById(2);
        $this->assertEquals(2, $member->getId());
        $this->assertEquals(1, $member->getCreatedAt());
    }

    public function testGetMemberByUserId(): void
    {
        $member = $this->podium()->member->getByUserId('10');
        $this->assertEquals(2, $member->getId());
    }

    public function testNonExistingMember(): void
    {
        $this->assertEmpty($this->podium()->member->getById(999));
    }

    public function testGetMembersByFilterEmpty(): void
    {
        $members = $this->podium()->member->getAll();
        $this->assertEquals(2, $members->getTotalCount());
        $this->assertEquals([2, 3], $members->getKeys());
    }

    public function testGetMembersByFilter(): void
    {
        $filter = new ActiveDataFilter([
            'searchModel' => static function () {
                return (new DynamicModel(['id']))->addRule('id', 'integer');
            }
        ]);
        $filter->load(['filter' => ['id' => 3]], '');

        $members = $this->podium()->member->getAll($filter);

        $this->assertEquals(1, $members->getTotalCount());
        $this->assertEquals([3], $members->getKeys());
    }

    public function testGetMembersByFilterWithSorter(): void
    {
        $members = $this->podium()->member->getAll(null, ['defaultOrder' => ['id' => SORT_DESC]]);
        $this->assertEquals(2, $members->getTotalCount());
        $this->assertEquals([3, 2], $members->getKeys());
    }

    public function testGetMembersByFilterWithPagination(): void
    {
        $members = $this->podium()->member->getAll(null, null, ['defaultPageSize' => 1]);
        $this->assertEquals(2, $members->getTotalCount());
        $this->assertEquals([2], $members->getKeys());
    }

    public function testGetPostsCount(): void
    {
        $member = $this->podium()->member->getById(2);
        $this->assertEquals(3, $member->getPostsCount());
    }

    /**
     * @throws NotSupportedException
     */
    public function testGetParent(): void
    {
        $this->expectException(NotSupportedException::class);
        (new Member())->getParent();
    }

    /**
     * @throws NotSupportedException
     */
    public function testIsArchived(): void
    {
        $this->expectException(NotSupportedException::class);
        (new Member())->isArchived();
    }

    public function testGetUsername(): void
    {
        $member = $this->podium()->member->getById(3);
        $this->assertEquals('member3', $member->getUsername());
    }

    /**
     * @throws InvalidConfigException
     */
    public function testConvert(): void
    {
        $member = Member::findById(2);
        $this->assertInstanceOf(MemberRemover::class, $member->convert(MemberRemover::class));
    }

    /**
     * @throws InvalidConfigException
     */
    public function testWrongConvert(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $member = Member::findById(2);
        $member->convert('stdClass');
    }
}
