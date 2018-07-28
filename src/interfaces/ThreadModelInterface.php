<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

use yii\data\DataFilter;
use yii\data\DataProviderInterface;
use yii\data\Pagination;
use yii\data\Sort;

/**
 * Interface ThreadModelInterface
 * @package bizley\podium\api\interfaces
 */
interface ThreadModelInterface
{
    /**
     * @param int $threadId
     * @return ThreadModelInterface|null
     */
    public static function findThreadById(int $threadId): ?ThreadModelInterface;

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
    public static function findThreads(?DataFilter $filter = null, $sort = null, $pagination = null): DataProviderInterface;
}
