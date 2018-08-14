<?php

declare(strict_types=1);

namespace bizley\podium\api\models\category;

use bizley\podium\api\events\ArchiveEvent;
use bizley\podium\api\interfaces\ArchivableInterface;
use bizley\podium\api\repos\CategoryRepo;
use Yii;

/**
 * Class CategoryArchiver
 * @package bizley\podium\api\models\category
 */
class CategoryArchiver extends CategoryRepo implements ArchivableInterface
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
     * @return bool
     */
    public function archive(): bool
    {
        if (!$this->beforeArchive()) {
            return false;
        }
        if ($this->archived) {
            $this->addError('archived', Yii::t('podium.error', 'category.already.archived'));
            return false;
        }

        $this->archived = true;
        if (!$this->save()) {
            Yii::error('Error while archiving category', 'podium');
            return false;
        }

        $this->afterArchive();
        return true;
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
     * @return bool
     */
    public function revive(): bool
    {
        if (!$this->beforeRevive()) {
            return false;
        }
        if (!$this->archived) {
            $this->addError('archived', Yii::t('podium.error', 'category.not.archived'));
            return false;
        }

        $this->archived = false;
        if (!$this->save()) {
            Yii::error('Error while reviving category', 'podium');
            return false;
        }

        $this->afterRevive();
        return true;
    }

    public function afterRevive(): void
    {
        $this->trigger(self::EVENT_AFTER_REVIVING, new ArchiveEvent([
            'model' => $this
        ]));
    }
}
