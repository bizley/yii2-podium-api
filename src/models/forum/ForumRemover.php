<?php

declare(strict_types=1);

namespace bizley\podium\api\models\forum;

use bizley\podium\api\base\PodiumResponse;
use bizley\podium\api\events\RemoveEvent;
use bizley\podium\api\interfaces\RemovableInterface;
use bizley\podium\api\repos\ForumRepo;
use Yii;

/**
 * Class ForumRemover
 * @package bizley\podium\api\models\forum
 */
class ForumRemover extends ForumRepo implements RemovableInterface
{
    public const EVENT_BEFORE_REMOVING = 'podium.forum.removing.before';
    public const EVENT_AFTER_REMOVING = 'podium.forum.removing.after';

    /**
     * @param int $modelId
     * @return RemovableInterface|null
     */
    public static function findById(int $modelId): ?RemovableInterface
    {
        return static::findOne(['id' => $modelId]);
    }

    /**
     * @return bool
     */
    public function beforeRemove(): bool
    {
        $event = new RemoveEvent();
        $this->trigger(self::EVENT_BEFORE_REMOVING, $event);

        return $event->canRemove;
    }

    /**
     * @return PodiumResponse
     */
    public function remove(): PodiumResponse
    {
        if (!$this->beforeRemove()) {
            return PodiumResponse::error();
        }

        if (!$this->archived) {
            $this->addError('archived', Yii::t('podium.error', 'forum.must.be.archived'));
            return PodiumResponse::error($this);
        }

        try {
            if ($this->delete() === false) {
                Yii::error('Error while deleting forum', 'podium');
                return PodiumResponse::error();
            }

            $this->afterRemove();

            return PodiumResponse::success();

        } catch (\Throwable $exc) {
            Yii::error(['Exception while removing forum', $exc->getMessage(), $exc->getTraceAsString()], 'podium');
            return PodiumResponse::error();
        }
    }

    public function afterRemove(): void
    {
        $this->trigger(self::EVENT_AFTER_REMOVING);
    }
}
