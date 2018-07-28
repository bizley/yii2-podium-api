<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

use yii\data\DataFilter;
use yii\data\DataProviderInterface;
use yii\data\Pagination;
use yii\data\Sort;

/**
 * Interface CategoryModelInterface
 * @package bizley\podium\api\interfaces
 */
interface CategoryModelInterface
{
    /**
     * @param int $categoryId
     * @return CategoryModelInterface|null
     */
    public static function findCategoryById(int $categoryId): ?CategoryModelInterface;

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
    public static function findCategories(?DataFilter $filter = null, $sort = null, $pagination = null): DataProviderInterface;
}
