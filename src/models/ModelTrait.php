<?php

declare(strict_types=1);

namespace bizley\podium\api\models;

use bizley\podium\api\interfaces\ModelInterface;
use yii\data\ActiveDataProvider;
use yii\data\DataFilter;
use yii\data\DataProviderInterface;
use yii\data\Pagination;
use yii\data\Sort;

/**
 * Trait ModelTrait
 * @package bizley\podium\api\models
 */
trait ModelTrait
{
    /**
     * @param int $modelId
     * @return ModelInterface|null
     */
    public static function findById(int $modelId): ?ModelInterface
    {
        return static::findOne(['id' => $modelId]);
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

        $dataProvider = new ActiveDataProvider(['query' => $query]);

        if ($sort !== null) {
            $dataProvider->setSort($sort);
        }
        if ($pagination !== null) {
            $dataProvider->setPagination($pagination);
        }

        return $dataProvider;
    }
}
