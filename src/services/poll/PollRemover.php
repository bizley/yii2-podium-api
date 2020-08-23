<?php

declare(strict_types=1);

namespace bizley\podium\api\services\poll;

use bizley\podium\api\components\PodiumResponse;
use bizley\podium\api\events\RemoveEvent;
use bizley\podium\api\interfaces\PollRepositoryInterface;
use bizley\podium\api\interfaces\RemoverInterface;
use bizley\podium\api\interfaces\RepositoryInterface;
use Throwable;
use Yii;
use yii\base\Component;

final class PollRemover extends Component implements RemoverInterface
{
    public const EVENT_BEFORE_REMOVING = 'podium.poll.removing.before';
    public const EVENT_AFTER_REMOVING = 'podium.poll.removing.after';

    public function beforeRemove(): bool
    {
        $event = new RemoveEvent();
        $this->trigger(self::EVENT_BEFORE_REMOVING, $event);

        return $event->canRemove;
    }

    /**
     * Removes the poll.
     */
    public function remove(RepositoryInterface $poll): PodiumResponse
    {
        if (!$poll instanceof PollRepositoryInterface || !$this->beforeRemove()) {
            return PodiumResponse::error();
        }

        try {
            if (!$poll->isArchived()) {
                return PodiumResponse::error(['api' => Yii::t('podium.error', 'poll.must.be.archived')]);
            }

            if (!$poll->delete()) {
                return PodiumResponse::error();
            }

            $this->afterRemove();

            return PodiumResponse::success();
        } catch (Throwable $exc) {
            Yii::error(['Exception while deleting poll', $exc->getMessage(), $exc->getTraceAsString()], 'podium');

            return PodiumResponse::error();
        }
    }

    public function afterRemove(): void
    {
        $this->trigger(self::EVENT_AFTER_REMOVING);
    }
}
