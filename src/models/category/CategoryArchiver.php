<?php

declare(strict_types=1);

namespace bizley\podium\api\models\category;

use bizley\podium\api\components\PodiumResponse;
use bizley\podium\api\events\ArchiveEvent;
use bizley\podium\api\interfaces\ArchiverInterface;
use Yii;

/**
 * Class CategoryArchiver
 * @package bizley\podium\api\models\category
 */
class CategoryArchiver extends Category implements ArchiverInterface
{
    public const EVENT_BEFORE_ARCHIVING = 'podium.category.archiving.before';
    public const EVENT_AFTER_ARCHIVING = 'podium.category.archiving.after';
    public const EVENT_BEFORE_REVIVING = 'podium.category.reviving.before';
    public const EVENT_AFTER_REVIVING = 'podium.category.reviving.after';

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

        if ($this->isArchived()) {
            $this->addError('archived', Yii::t('podium.error', 'category.already.archived'));

            return PodiumResponse::error($this);
        }

        $this->archived = true;

        if (!$this->save()) {
            Yii::error(['Error while archiving category', $this->errors], 'podium');

            return PodiumResponse::error($this);
        }

        $this->afterArchive();

        return PodiumResponse::success();
    }

    public function afterArchive(): void
    {
        $this->trigger(self::EVENT_AFTER_ARCHIVING, new ArchiveEvent(['model' => $this]));
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

        if (!$this->isArchived()) {
            $this->addError('archived', Yii::t('podium.error', 'category.not.archived'));

            return PodiumResponse::error($this);
        }

        $this->archived = false;

        if (!$this->save()) {
            Yii::error(['Error while reviving category', $this->errors], 'podium');

            return PodiumResponse::error($this);
        }

        $this->afterRevive();

        return PodiumResponse::success();
    }

    public function afterRevive(): void
    {
        $this->trigger(self::EVENT_AFTER_REVIVING, new ArchiveEvent(['model' => $this]));
    }
}
