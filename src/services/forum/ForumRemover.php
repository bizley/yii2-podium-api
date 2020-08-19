<?php

declare(strict_types=1);

namespace bizley\podium\api\services\forum;

use bizley\podium\api\components\PodiumResponse;
use bizley\podium\api\events\RemoveEvent;
use bizley\podium\api\interfaces\ForumRepositoryInterface;
use bizley\podium\api\interfaces\RemoverInterface;
use bizley\podium\api\repositories\ForumRepository;
use Throwable;
use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\di\Instance;

final class ForumRemover extends Component implements RemoverInterface
{
    public const EVENT_BEFORE_REMOVING = 'podium.forum.removing.before';
    public const EVENT_AFTER_REMOVING = 'podium.forum.removing.after';

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

    public function beforeRemove(): bool
    {
        $event = new RemoveEvent();
        $this->trigger(self::EVENT_BEFORE_REMOVING, $event);

        return $event->canRemove;
    }

    /**
     * Removes the forum.
     */
    public function remove($id): PodiumResponse
    {
        if (!$this->beforeRemove()) {
            return PodiumResponse::error();
        }

        try {
            $forum = $this->getForum();
            if (!$forum->fetchOne($id)) {
                return PodiumResponse::error(['api' => Yii::t('podium.error', 'forum.not.exists')]);
            }
            if (!$forum->isArchived()) {
                return PodiumResponse::error(['api' => Yii::t('podium.error', 'forum.must.be.archived')]);
            }

            if (!$forum->delete()) {
                return PodiumResponse::error();
            }

            $this->afterRemove();

            return PodiumResponse::success();
        } catch (Throwable $exc) {
            Yii::error(['Exception while deleting forum', $exc->getMessage(), $exc->getTraceAsString()], 'podium');

            return PodiumResponse::error();
        }
    }

    public function afterRemove(): void
    {
        $this->trigger(self::EVENT_AFTER_REMOVING);
    }
}
