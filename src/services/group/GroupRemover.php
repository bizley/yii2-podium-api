<?php

declare(strict_types=1);

namespace bizley\podium\api\services\group;

use bizley\podium\api\components\PodiumResponse;
use bizley\podium\api\events\RemoveEvent;
use bizley\podium\api\interfaces\GroupRepositoryInterface;
use bizley\podium\api\interfaces\RemoverInterface;
use bizley\podium\api\repositories\GroupRepository;
use Throwable;
use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\di\Instance;

final class GroupRemover extends Component implements RemoverInterface
{
    public const EVENT_BEFORE_REMOVING = 'podium.group.removing.before';
    public const EVENT_AFTER_REMOVING = 'podium.group.removing.after';

    private ?GroupRepositoryInterface $group = null;

    /**
     * @var string|array|GroupRepositoryInterface
     */
    public $repositoryConfig = GroupRepository::class;

    /**
     * @throws InvalidConfigException
     */
    private function getGroup(): GroupRepositoryInterface
    {
        if (null === $this->group) {
            /** @var GroupRepositoryInterface $group */
            $group = Instance::ensure($this->repositoryConfig, GroupRepositoryInterface::class);
            $this->group = $group;
        }

        return $this->group;
    }

    public function beforeRemove(): bool
    {
        $event = new RemoveEvent();
        $this->trigger(self::EVENT_BEFORE_REMOVING, $event);

        return $event->canRemove;
    }

    /**
     * Removes the thread.
     */
    public function remove($id): PodiumResponse
    {
        if (!$this->beforeRemove()) {
            return PodiumResponse::error();
        }

        try {
            $group = $this->getGroup();
            if (!$group->fetchOne($id)) {
                return PodiumResponse::error(['api' => Yii::t('podium.error', 'group.not.exists')]);
            }

            if (!$group->delete()) {
                return PodiumResponse::error();
            }

            $this->afterRemove();

            return PodiumResponse::success();
        } catch (Throwable $exc) {
            Yii::error(['Exception while deleting group', $exc->getMessage(), $exc->getTraceAsString()], 'podium');

            return PodiumResponse::error();
        }
    }

    public function afterRemove(): void
    {
        $this->trigger(self::EVENT_AFTER_REMOVING);
    }
}
