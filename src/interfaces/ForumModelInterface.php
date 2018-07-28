<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

use yii\data\DataFilter;
use yii\data\DataProviderInterface;
use yii\data\Pagination;
use yii\data\Sort;

/**
 * Interface ForumModelInterface
 * @package bizley\podium\api\interfaces
 */
interface ForumModelInterface
{
    /**
     * @param int $forumId
     * @return ForumModelInterface|null
     */
    public static function findForumById(int $forumId): ?ForumModelInterface;

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
    public static function findForums(?DataFilter $filter = null, $sort = null, $pagination = null): DataProviderInterface;
}
