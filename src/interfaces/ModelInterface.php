<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

use yii\data\DataFilter;
use yii\data\DataProviderInterface;
use yii\data\Pagination;
use yii\data\Sort;

/**
 * Interface ModelInterface
 * @package bizley\podium\api\interfaces
 */
interface ModelInterface
{
    /**
     * @param int $modelId
     * @return ModelInterface|null
     */
    public static function findById(int $modelId): ?ModelInterface;

    /**
     * @return int
     */
    public function getId(): int;

    /**
     * @param DataFilter|null $filter
     * @param Sort|array|bool|null $sort
     * @param Pagination|array|bool|null $pagination
     * @return DataProviderInterface
     */
    public static function findByFilter(?DataFilter $filter = null, $sort = null, $pagination = null): DataProviderInterface;

    /**
     * @return int|false
     */
    public function delete();
}
