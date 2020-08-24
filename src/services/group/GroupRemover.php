<?php

declare(strict_types=1);

namespace bizley\podium\api\services\group;

use bizley\podium\api\components\PodiumResponse;
use bizley\podium\api\events\RemoveEvent;
use bizley\podium\api\interfaces\GroupRepositoryInterface;
use bizley\podium\api\interfaces\RemoverInterface;
use bizley\podium\api\interfaces\RepositoryInterface;
use Throwable;
use Yii;
use yii\base\Component;

final class GroupRemover extends Component implements RemoverInterface
{
    public const EVENT_BEFORE_REMOVING = 'podium.group.removing.before';
    public const EVENT_AFTER_REMOVING = 'podium.group.removing.after';

    public function beforeRemove(): bool
    {
        $event = new RemoveEvent();
        $this->trigger(self::EVENT_BEFORE_REMOVING, $event);

        return $event->canRemove;
    }

    /**
     * Removes the group.
     */
    public function remove(RepositoryInterface $group): PodiumResponse
    {
        if (!$group instanceof GroupRepositoryInterface || !$this->beforeRemove()) {
            return PodiumResponse::error();
        }

        try {
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
