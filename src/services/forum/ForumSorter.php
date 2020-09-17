<?php

declare(strict_types=1);

namespace bizley\podium\api\services\forum;

use bizley\podium\api\components\PodiumResponse;
use bizley\podium\api\events\SortEvent;
use bizley\podium\api\interfaces\ForumRepositoryInterface;
use bizley\podium\api\interfaces\RepositoryInterface;
use bizley\podium\api\interfaces\SorterInterface;
use bizley\podium\api\repositories\ForumRepository;
use Throwable;
use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\db\Exception;
use yii\db\Transaction;
use yii\di\Instance;

final class ForumSorter extends Component implements SorterInterface
{
    public const EVENT_BEFORE_REPLACING = 'podium.forum.replacing.before';
    public const EVENT_AFTER_REPLACING = 'podium.forum.replacing.after';
    public const EVENT_BEFORE_SORTING = 'podium.forum.sorting.before';
    public const EVENT_AFTER_SORTING = 'podium.forum.sorting.after';

    private ?ForumRepositoryInterface $forum = null;

    /**
     * @var string|array|ForumRepositoryInterface
     */
    public $repositoryConfig = ForumRepository::class;

    /**
     * @throws InvalidConfigException
     */
    private function getForum(): ForumRepositoryInterface
    {
        if (null === $this->forum) {
            /** @var ForumRepositoryInterface $forum */
            $forum = Instance::ensure($this->repositoryConfig, ForumRepositoryInterface::class);
            $this->forum = $forum;
        }

        return $this->forum;
    }

    /**
     * Calls before replacing the order of forums.
     */
    public function beforeReplace(): bool
    {
        $event = new SortEvent();
        $this->trigger(self::EVENT_BEFORE_REPLACING, $event);

        return $event->canReplace;
    }

    /**
     * Replaces the spot of the forums.
     */
    public function replace(RepositoryInterface $firstForum, RepositoryInterface $secondForum): PodiumResponse
    {
        if (
            !$firstForum instanceof ForumRepositoryInterface
            || !$secondForum instanceof ForumRepositoryInterface
            || !$this->beforeReplace()
        ) {
            return PodiumResponse::error();
        }

        /** @var Transaction $transaction */
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $oldOrder = $firstForum->getOrder();
            if (!$firstForum->setOrder($secondForum->getOrder())) {
                throw new Exception('Error while setting new forum order!');
            }
            if (!$secondForum->setOrder($oldOrder)) {
                throw new Exception('Error while setting new forum order!');
            }

            $this->afterReplace();
            $transaction->commit();

            return PodiumResponse::success();
        } catch (Throwable $exc) {
            $transaction->rollBack();
            Yii::error(
                ['Exception while replacing forums order', $exc->getMessage(), $exc->getTraceAsString()],
                'podium'
            );

            return PodiumResponse::error(['exception' => $exc]);
        }
    }

    /**
     * Calls after replacing the forums order successfully.
     */
    public function afterReplace(): void
    {
        $this->trigger(self::EVENT_AFTER_REPLACING);
    }

    /**
     * Calls before sorting the forums.
     */
    public function beforeSort(): bool
    {
        $event = new SortEvent();
        $this->trigger(self::EVENT_BEFORE_SORTING, $event);

        return $event->canSort;
    }

    /**
     * Sorts the forums.
     */
    public function sort(): PodiumResponse
    {
        if (!$this->beforeSort()) {
            return PodiumResponse::error();
        }

        /** @var Transaction $transaction */
        $transaction = Yii::$app->db->beginTransaction();
        try {
            if (!$this->getForum()->sort()) {
                return PodiumResponse::error();
            }

            $this->afterSort();
            $transaction->commit();

            return PodiumResponse::success();
        } catch (Throwable $exc) {
            $transaction->rollBack();
            Yii::error(['Exception while sorting forums', $exc->getMessage(), $exc->getTraceAsString()], 'podium');

            return PodiumResponse::error(['exception' => $exc]);
        }
    }

    /**
     * Calls after sorting forums successfully.
     */
    public function afterSort(): void
    {
        $this->trigger(self::EVENT_AFTER_SORTING);
    }
}
