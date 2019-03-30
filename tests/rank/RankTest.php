<?php

declare(strict_types=1);

namespace bizley\podium\tests\rank;

use bizley\podium\api\models\rank\Rank;
use bizley\podium\tests\DbTestCase;
use yii\base\DynamicModel;
use yii\base\NotSupportedException;
use yii\data\ActiveDataFilter;

/**
 * Class RankTest
 * @package bizley\podium\tests\rank
 */
class RankTest extends DbTestCase
{
    /**
     * @var array
     */
    public $fixtures = [
        'podium_rank' => [
            [
                'id' => 1,
                'name' => 'rank1',
                'min_posts' => 0,
                'created_at' => 1,
                'updated_at' => 1,
            ],
            [
                'id' => 2,
                'name' => 'rank2',
                'min_posts' => 10,
                'created_at' => 1,
                'updated_at' => 1,
            ],
        ],
    ];

    public function testGetRankById(): void
    {
        $rank = $this->podium()->rank->getRankById(1);
        $this->assertEquals(1, $rank->getId());
    }

    public function testNonExistingRank(): void
    {
        $this->assertEmpty($this->podium()->rank->getRankById(999));
    }

    public function testGetRanksByFilterEmpty(): void
    {
        $ranks = $this->podium()->rank->getRanks();
        $this->assertEquals(2, $ranks->getTotalCount());
        $this->assertEquals([1, 2], $ranks->getKeys());
    }

    public function testGetRanksByFilter(): void
    {
        $filter = new ActiveDataFilter([
            'searchModel' => static function () {
                return (new DynamicModel(['id']))->addRule('id', 'integer');
            }
        ]);
        $filter->load(['filter' => ['id' => 2]], '');

        $ranks = $this->podium()->rank->getRanks($filter);

        $this->assertEquals(1, $ranks->getTotalCount());
        $this->assertEquals([2], $ranks->getKeys());
    }

    /**
     * @throws NotSupportedException
     */
    public function testGetPostsCount(): void
    {
        $this->expectException(NotSupportedException::class);
        (new Rank())->getPostsCount();
    }

    /**
     * @throws NotSupportedException
     */
    public function testIsArchived(): void
    {
        $this->expectException(NotSupportedException::class);
        (new Rank())->isArchived();
    }

    /**
     * @throws NotSupportedException
     */
    public function testGetParent(): void
    {
        $this->expectException(NotSupportedException::class);
        (new Rank())->getParent();
    }
}
