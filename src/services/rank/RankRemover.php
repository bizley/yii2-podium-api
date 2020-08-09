<?php

declare(strict_types=1);

namespace bizley\podium\api\services\rank;

use bizley\podium\api\components\PodiumResponse;
use bizley\podium\api\events\RemoveEvent;
use bizley\podium\api\interfaces\RankRepositoryInterface;
use bizley\podium\api\interfaces\RemoverInterface;
use bizley\podium\api\repositories\RankRepository;
use Throwable;
use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\di\Instance;

final class RankRemover extends Component implements RemoverInterface
{
    public const EVENT_BEFORE_REMOVING = 'podium.rank.removing.before';
    public const EVENT_AFTER_REMOVING = 'podium.rank.removing.after';

    private ?RankRepositoryInterface $rank = null;

    /**
     * @var string|array|RankRepositoryInterface
     */
    public $repositoryConfig = RankRepository::class;

    /**
     * @throws InvalidConfigException
     */
    private function getRank(): RankRepositoryInterface
    {
        if (null === $this->rank) {
            /** @var RankRepositoryInterface $rank */
            $rank = Instance::ensure($this->repositoryConfig, RankRepositoryInterface::class);
            $this->rank = $rank;
        }

        return $this->rank;
    }

    /**
     * Executes before remove().
     */
    public function beforeRemove(): bool
    {
        $event = new RemoveEvent();
        $this->trigger(self::EVENT_BEFORE_REMOVING, $event);

        return $event->canRemove;
    }

    /**
     * Removes the rank.
     */
    public function remove(int $id): PodiumResponse
    {
        if (!$this->beforeRemove()) {
            return PodiumResponse::error();
        }

        try {
            $rank = $this->getRank();
            if (!$rank->fetchOne($id)) {
                return PodiumResponse::error(['api' => Yii::t('podium.error', 'rank.not.exists')]);
            }
            if (!$rank->delete()) {
                return PodiumResponse::error();
            }

            $this->afterRemove();

            return PodiumResponse::success();
        } catch (Throwable $exc) {
            Yii::error(['Exception while removing rank', $exc->getMessage(), $exc->getTraceAsString()], 'podium');

            return PodiumResponse::error();
        }
    }

    public function afterRemove(): void
    {
        $this->trigger(self::EVENT_AFTER_REMOVING);
    }
}
