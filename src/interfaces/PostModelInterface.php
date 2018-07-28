<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

use yii\data\DataFilter;
use yii\data\DataProviderInterface;
use yii\data\Pagination;
use yii\data\Sort;

/**
 * Interface PostModelInterface
 * @package bizley\podium\api\interfaces
 */
interface PostModelInterface
{
    /**
     * @param int $postId
     * @return PostModelInterface|null
     */
    public static function findPostById(int $postId): ?PostModelInterface;

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
