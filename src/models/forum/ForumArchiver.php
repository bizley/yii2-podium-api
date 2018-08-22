<?php

declare(strict_types=1);

namespace bizley\podium\api\models\forum;

use bizley\podium\api\base\PodiumResponse;
use bizley\podium\api\events\ArchiveEvent;
use bizley\podium\api\interfaces\ArchivableInterface;
use bizley\podium\api\repos\ForumRepo;
use Yii;

/**
 * Class ForumArchiver
 * @package bizley\podium\api\models\forum
 */
class ForumArchiver extends ForumRepo implements ArchivableInterface
{
    public const EVENT_BEFORE_ARCHIVING = 'podium.forum.archiving.before';
    public const EVENT_AFTER_ARCHIVING = 'podium.forum.archiving.after';
    public const EVENT_BEFORE_REVIVING = 'podium.forum.reviving.before';
    public const EVENT_AFTER_REVIVING = 'podium.forum.reviving.after';

    /**
     * @return bool
     */
    public function beforeArchive(): bool
    {
        $event = new ArchiveEvent();
        $this->trigger(self::EVENT_BEFORE_ARCHIVING, $event);

        return $event->canArchive;
    }

    /**
     * @return PodiumResponse
     */
    public function archive(): PodiumResponse
    {
        if (!$this->beforeArchive()) {
            return PodiumResponse::error();
        }
        if ($this->archived) {
            $this->addError('archived', Yii::t('podium.error', 'forum.already.archived'));
            return PodiumResponse::error($this);
        }

        $this->archived = true;
        if (!$this->save()) {
            Yii::error('Error while archiving forum', 'podium');
            return PodiumResponse::error($this);
        }

        $this->afterArchive();
        return PodiumResponse::success();
    }

    public function afterArchive(): void
    {
        $this->trigger(self::EVENT_AFTER_ARCHIVING, new ArchiveEvent([
            'model' => $this
        ]));
    }

    /**
     * @return bool
     */
    public function beforeRevive(): bool
    {
        $event = new ArchiveEvent();
        $this->trigger(self::EVENT_BEFORE_REVIVING, $event);

        return $event->canRevive;
    }

    /**
     * @return PodiumResponse
     */
    public function revive(): PodiumResponse
    {
        if (!$this->beforeRevive()) {
            return PodiumResponse::error();
        }
        if (!$this->archived) {
            $this->addError('archived', Yii::t('podium.error', 'forum.not.archived'));
            return PodiumResponse::error($this);
        }

        $this->archived = false;
        if (!$this->save()) {
            Yii::error('Error while reviving forum', 'podium');
            return PodiumResponse::error($this);
        }

        $this->afterRevive();
        return PodiumResponse::success();
    }

    public function afterRevive(): void
    {
        $this->trigger(self::EVENT_AFTER_REVIVING, new ArchiveEvent([
            'model' => $this
        ]));
    }
}
