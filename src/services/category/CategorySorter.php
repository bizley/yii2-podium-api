<?php

declare(strict_types=1);

namespace bizley\podium\api\services\category;

use bizley\podium\api\components\PodiumResponse;
use bizley\podium\api\events\SortEvent;
use bizley\podium\api\interfaces\CategoryRepositoryInterface;
use bizley\podium\api\interfaces\RepositoryInterface;
use bizley\podium\api\interfaces\SorterInterface;
use bizley\podium\api\repositories\CategoryRepository;
use Throwable;
use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\db\Exception;
use yii\db\Transaction;
use yii\di\Instance;

final class CategorySorter extends Component implements SorterInterface
{
    public const EVENT_BEFORE_REPLACING = 'podium.category.replacing.before';
    public const EVENT_AFTER_REPLACING = 'podium.category.replacing.after';
    public const EVENT_BEFORE_SORTING = 'podium.category.sorting.before';
    public const EVENT_AFTER_SORTING = 'podium.category.sorting.after';

    private ?CategoryRepositoryInterface $category = null;

    /**
     * @var string|array|CategoryRepositoryInterface
     */
    public $repositoryConfig = CategoryRepository::class;

    /**
     * @throws InvalidConfigException
     */
    private function getCategory(): CategoryRepositoryInterface
    {
        if (null === $this->category) {
            /** @var CategoryRepositoryInterface $category */
            $category = Instance::ensure($this->repositoryConfig, CategoryRepositoryInterface::class);
            $this->category = $category;
        }

        return $this->category;
    }

    /**
     * Calls before replacing the order of categories.
     */
    public function beforeReplace(): bool
    {
        $event = new SortEvent();
        $this->trigger(self::EVENT_BEFORE_REPLACING, $event);

        return $event->canReplace;
    }

    /**
     * Replaces the spot of the categories.
     */
    public function replace(RepositoryInterface $firstCategory, RepositoryInterface $secondCategory): PodiumResponse
    {
        if (
            !$firstCategory instanceof CategoryRepositoryInterface
            || !$secondCategory instanceof CategoryRepositoryInterface
            || !$this->beforeReplace()
        ) {
            return PodiumResponse::error();
        }

        /** @var Transaction $transaction */
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $oldOrder = $firstCategory->getOrder();
            if (!$firstCategory->setOrder($secondCategory->getOrder())) {
                throw new Exception('Error while setting new category order!');
            }
            if (!$secondCategory->setOrder($oldOrder)) {
                throw new Exception('Error while setting new category order!');
            }

            $this->afterReplace();
            $transaction->commit();

            return PodiumResponse::success();
        } catch (Throwable $exc) {
            $transaction->rollBack();
            Yii::error(
                ['Exception while replacing categories order', $exc->getMessage(), $exc->getTraceAsString()],
                'podium'
            );

            return PodiumResponse::error(['exception' => $exc]);
        }
    }

    /**
     * Calls after successful replacing the order of categories.
     */
    public function afterReplace(): void
    {
        $this->trigger(self::EVENT_AFTER_REPLACING);
    }

    /**
     * Calls before sorting categories.
     */
    public function beforeSort(): bool
    {
        $event = new SortEvent();
        $this->trigger(self::EVENT_BEFORE_SORTING, $event);

        return $event->canSort;
    }

    /**
     * Sorts the categories.
     */
    public function sort(): PodiumResponse
    {
        if (!$this->beforeSort()) {
            return PodiumResponse::error();
        }

        /** @var Transaction $transaction */
        $transaction = Yii::$app->db->beginTransaction();
        try {
            if (!$this->getCategory()->sort()) {
                return PodiumResponse::error();
            }

            $this->afterSort();
            $transaction->commit();

            return PodiumResponse::success();
        } catch (Throwable $exc) {
            $transaction->rollBack();
            Yii::error(
                ['Exception while sorting categories', $exc->getMessage(), $exc->getTraceAsString()],
                'podium'
            );

            return PodiumResponse::error(['exception' => $exc]);
        }
    }

    /**
     * Calls after successful sorting of categories.
     */
    public function afterSort(): void
    {
        $this->trigger(self::EVENT_AFTER_SORTING);
    }
}
