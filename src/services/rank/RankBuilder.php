<?php

declare(strict_types=1);

namespace bizley\podium\api\services\rank;

use bizley\podium\api\components\PodiumResponse;
use bizley\podium\api\events\ModelEvent;
use bizley\podium\api\interfaces\BuilderInterface;
use bizley\podium\api\interfaces\RankRepositoryInterface;
use bizley\podium\api\repositories\RankRepository;
use Throwable;
use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\di\Instance;

final class RankBuilder extends Component implements BuilderInterface
{
    public const EVENT_BEFORE_CREATING = 'podium.rank.creating.before';
    public const EVENT_AFTER_CREATING = 'podium.rank.creating.after';
    public const EVENT_BEFORE_EDITING = 'podium.rank.editing.before';
    public const EVENT_AFTER_EDITING = 'podium.rank.editing.after';

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

    public function beforeCreate(): bool
    {
        $event = new ModelEvent();
        $this->trigger(self::EVENT_BEFORE_CREATING, $event);

        return $event->canCreate;
    }

    /**
     * Creates new rank.
     */
    public function create(array $data): PodiumResponse
    {
        if (!$this->beforeCreate()) {
            return PodiumResponse::error();
        }

        try {
            $rank = $this->getRank();

            if (!$rank->create($data)) {
                return PodiumResponse::error($rank->getErrors());
            }

            $this->afterCreate();

            return PodiumResponse::success();
        } catch (Throwable $exc) {
            Yii::error(['Exception while creating rank', $exc->getMessage(), $exc->getTraceAsString()], 'podium');

            return PodiumResponse::error();
        }
    }

    public function afterCreate(): void
    {
        $this->trigger(self::EVENT_AFTER_CREATING, new ModelEvent(['model' => $this]));
    }

    public function beforeEdit(): bool
    {
        $event = new ModelEvent();
        $this->trigger(self::EVENT_BEFORE_EDITING, $event);

        return $event->canEdit;
    }

    /**
     * Edits the thread.
     */
    public function edit(int $id, array $data): PodiumResponse
    {
        if (!$this->beforeEdit()) {
            return PodiumResponse::error();
        }

        try {
            $rank = $this->getRank();
            if (!$rank->fetchOne($id)) {
                return PodiumResponse::error(['api' => Yii::t('podium.error', 'rank.not.exists')]);
            }

            if (!$rank->edit($data)) {
                return PodiumResponse::error($rank->getErrors());
            }

            $this->afterEdit();

            return PodiumResponse::success();
        } catch (Throwable $exc) {
            Yii::error(['Exception while editing rank', $exc->getMessage(), $exc->getTraceAsString()], 'podium');

            return PodiumResponse::error();
        }
    }

    public function afterEdit(): void
    {
        $this->trigger(self::EVENT_AFTER_EDITING, new ModelEvent(['model' => $this]));
    }
}
