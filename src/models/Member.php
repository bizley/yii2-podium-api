<?php

declare(strict_types=1);

namespace bizley\podium\api\models;

use bizley\podium\api\interfaces\MembershipInterface;
use bizley\podium\api\interfaces\ModelInterface;
use bizley\podium\api\repos\MemberRepo;
use yii\data\ActiveDataProvider;
use yii\data\DataFilter;
use yii\data\DataProviderInterface;
use yii\data\Pagination;
use yii\data\Sort;

/**
 * Class Member
 * @package bizley\podium\api\models
 */
class Member extends MemberRepo implements MembershipInterface
{
    /**
     * @param int $memberId
     * @return ModelInterface|null
     */
    public static function findById(int $memberId): ?ModelInterface
    {
        return static::findOne(['id' => $memberId]);
    }

    /**
     * @param int|string $userId
     * @return MembershipInterface|null
     */
    public static function findByUserId($userId): ?MembershipInterface
    {
        return static::findOne(['user_id' => $userId]);
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
    public static function findByFilter(?DataFilter $filter = null, $sort = null, $pagination = null): DataProviderInterface
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
