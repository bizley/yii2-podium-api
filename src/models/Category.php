<?php

declare(strict_types=1);

namespace bizley\podium\api\models;

use bizley\podium\api\interfaces\CategoryModelInterface;
use bizley\podium\api\interfaces\MembershipInterface;
use bizley\podium\api\repos\MemberRepo;
use yii\data\ActiveDataProvider;
use yii\data\DataFilter;
use yii\data\DataProviderInterface;
use yii\data\Pagination;
use yii\data\Sort;

/**
 * Class Category
 * @package bizley\podium\api\models
 */
class Category extends CategoryRepo implements CategoryModelInterface
{
    /**
     * @param int $memberId
     * @return MembershipInterface|null
     */
    public static function findMemberById(int $memberId): ?MembershipInterface
    {
        return static::findOne(['id' => $memberId]);
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param DataFilter|null $filter
     * @param Sort|array|bool|null $sort
     * @param Pagination|array|bool|null $pagination
     * @return ActiveDataProvider
     */
    public static function findMembers(?DataFilter $filter = null, $sort = null, $pagination = null): DataProviderInterface
    {
        $query = static::find();

        if ($filter !== null) {
            $filterConditions = $filter->build();
            if ($filterConditions !== false) {
                $query->andWhere($filterConditions);
            }
        }

        return new ActiveDataProvider([
            'query' => $query,
            'sort' => $sort,
            'pagination' => $pagination,
        ]);
    }
}
