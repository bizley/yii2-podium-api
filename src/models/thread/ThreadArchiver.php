<?php

declare(strict_types=1);

namespace bizley\podium\api\models\thread;

use bizley\podium\api\base\PodiumResponse;
use bizley\podium\api\events\ArchiveEvent;
use bizley\podium\api\interfaces\ArchivableInterface;
use bizley\podium\api\interfaces\ModelInterface;
use bizley\podium\api\models\forum\Forum;
use bizley\podium\api\repos\ThreadRepo;
use Yii;
use yii\db\Exception;

/**
 * Class ThreadRemover
 * @package bizley\podium\api\models\thread
 */
class ThreadArchiver extends ThreadRepo implements ArchivableInterface
{
    public const EVENT_BEFORE_ARCHIVING = 'podium.thread.archiving.before';
    public const EVENT_AFTER_ARCHIVING = 'podium.thread.archiving.after';
    public const EVENT_BEFORE_REVIVING = 'podium.thread.reviving.before';
    public const EVENT_AFTER_REVIVING = 'podium.thread.reviving.after';

    /**
     * @return ModelInterface
     */
    public function getForumModel(): ModelInterface
    {
        return Forum::findById($this->forum_id);
    }

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
            $this->addError('archived', Yii::t('podium.error', 'thread.already.archived'));
            return PodiumResponse::error($this);
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            if (!$this->getForumModel()->updateCounters([
                'threads_count' => -1,
                'posts_count' => -$this->posts_count,
            ])) {
                throw new Exception('Error while updating forum counters!');
            }

            $this->archived = true;
            if (!$this->save()) {
                Yii::error('Error while archiving thread', 'podium');
                throw new Exception('Error while archiving thread!');
            }

            $this->afterArchive();

            $transaction->commit();
            return PodiumResponse::success();

        } catch (\Throwable $exc) {
            Yii::error(['Exception while archiving thread', $exc->getMessage(), $exc->getTraceAsString()], 'podium');
            try {
                $transaction->rollBack();
            } catch (\Throwable $excTrans) {
                Yii::error(['Exception while thread archiving transaction rollback', $excTrans->getMessage(), $excTrans->getTraceAsString()], 'podium');
            }
        }
        return PodiumResponse::error();
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
            $this->addError('archived', Yii::t('podium.error', 'thread.not.archived'));
            return PodiumResponse::error($this);
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $this->archived = false;
            if (!$this->save()) {
                Yii::error(['Error while reviving thread', $this->errors], 'podium');
                return PodiumResponse::error($this);
            }

            if (!$this->getForumModel()->updateCounters([
                'threads_count' => 1,
                'posts_count' => $this->posts_count,
            ])) {
                throw new Exception('Error while updating forum counters!');
            }

            $this->afterRevive();

            $transaction->commit();
            return PodiumResponse::success();

        } catch (\Throwable $exc) {
            Yii::error(['Exception while reviving thread', $exc->getMessage(), $exc->getTraceAsString()], 'podium');
            try {
                $transaction->rollBack();
            } catch (\Throwable $excTrans) {
                Yii::error(['Exception while thread reviving transaction rollback', $excTrans->getMessage(), $excTrans->getTraceAsString()], 'podium');
            }
        }
        return PodiumResponse::error();
    }

    public function afterRevive(): void
    {
        $this->trigger(self::EVENT_AFTER_REVIVING, new ArchiveEvent([
            'model' => $this
        ]));
    }
}
