<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

use yii\base\InvalidConfigException;
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
     * @return int|null
     */
    public function getId(): ?int;

    /**
     * @return int
     */
    public function getCreatedAt(): int;

    /**
     * @param DataFilter|null $filter
     * @param Sort|array|bool|null $sort
     * @param Pagination|array|bool|null $pagination
     * @return DataProviderInterface
     */
    public static function findByFilter(?DataFilter $filter = null, $sort = null, $pagination = null): DataProviderInterface;

    /**
     * @return ModelInterface|null
     */
    public function getParent(): ?ModelInterface;

    /**
     * @param array $counters the counters to be updated (attribute name => increment value)
     * Use negative values if you want to decrement the counters.
     * @return bool whether the saving is successful
     */
    public function updateCounters($counters); // BC declaration

    /**
     * @param string $targetClass
     * @return mixed
     * @throws InvalidConfigException
     */
    public function convert(string $targetClass);

    /**
     * @return int
     */
    public function getPostsCount(): int;

    /**
     * @return bool
     */
    public function isArchived(): bool;

    /**
     * @return int|false
     */
    public function delete();
}
