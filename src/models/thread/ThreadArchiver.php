<?php

declare(strict_types=1);

namespace bizley\podium\api\models\thread;

use bizley\podium\api\base\PodiumResponse;
use bizley\podium\api\events\ArchiveEvent;
use bizley\podium\api\interfaces\ArchiverInterface;
use Throwable;
use Yii;
use yii\db\Exception;
use yii\db\Transaction;

/**
 * Class ThreadRemover
 * @package bizley\podium\api\models\thread
 */
class ThreadArchiver extends Thread implements ArchiverInterface
{
    public const EVENT_BEFORE_ARCHIVING = 'podium.thread.archiving.before';
    public const EVENT_AFTER_ARCHIVING = 'podium.thread.archiving.after';
    public const EVENT_BEFORE_REVIVING = 'podium.thread.reviving.before';
    public const EVENT_AFTER_REVIVING = 'podium.thread.reviving.after';

    /**
     * Executes before archive().
     * @return bool
     */
    public function beforeArchive(): bool
    {
        $event = new ArchiveEvent();
        $this->trigger(self::EVENT_BEFORE_ARCHIVING, $event);

        return $event->canArchive;
    }

    /**
     * Archives the thread.
     * @return PodiumResponse
     */
    public function archive(): PodiumResponse
    {
        if (!$this->beforeArchive()) {
            return PodiumResponse::error();
        }

        if ($this->isArchived()) {
            $this->addError('archived', Yii::t('podium.error', 'thread.already.archived'));

            return PodiumResponse::error($this);
        }

        $this->archived = true;

        if (!$this->validate()) {
            return PodiumResponse::error($this);
        }

        /** @var Transaction $transaction */
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $forum = $this->getParent();
            if ($forum === null) {
                throw new Exception('Can not find parent forum!');
            }

            if (
                !$forum->updateCounters(
                    [
                        'threads_count' => -1,
                        'posts_count' => -$this->posts_count,
                    ]
                )
            ) {
                throw new Exception('Error while updating forum counters!');
            }

            if (!$this->save(false)) {
                throw new Exception('Error while archiving thread!');
            }

            $this->afterArchive();
            $transaction->commit();

            return PodiumResponse::success();
        } catch (Throwable $exc) {
            $transaction->rollBack();
            Yii::error(['Exception while archiving thread', $exc->getMessage(), $exc->getTraceAsString()], 'podium');

            return PodiumResponse::error();
        }
    }

    /**
     * Executes after successful archive().
     */
    public function afterArchive(): void
    {
        $this->trigger(self::EVENT_AFTER_ARCHIVING, new ArchiveEvent(['model' => $this]));
    }

    /**
     * Executes before revive().
     * @return bool
     */
    public function beforeRevive(): bool
    {
        $event = new ArchiveEvent();
        $this->trigger(self::EVENT_BEFORE_REVIVING, $event);

        return $event->canRevive;
    }

    /**
     * Revives the thread.
     * @return PodiumResponse
     */
    public function revive(): PodiumResponse
    {
        if (!$this->beforeRevive()) {
            return PodiumResponse::error();
        }

        if (!$this->isArchived()) {
            $this->addError('archived', Yii::t('podium.error', 'thread.not.archived'));

            return PodiumResponse::error($this);
        }

        $this->archived = false;

        if (!$this->validate()) {
            return PodiumResponse::error($this);
        }

        /** @var Transaction $transaction */
        $transaction = Yii::$app->db->beginTransaction();
        try {
            if (!$this->save(false)) {
                throw new Exception('Error while reviving thread');
            }

            $forum = $this->getParent();
            if ($forum === null) {
                throw new Exception('Can not find parent forum!');
            }

            if (
                !$forum->updateCounters(
                    [
                        'threads_count' => 1,
                        'posts_count' => $this->posts_count,
                    ]
                )
            ) {
                throw new Exception('Error while updating forum counters!');
            }

            $this->afterRevive();
            $transaction->commit();

            return PodiumResponse::success();
        } catch (Throwable $exc) {
            $transaction->rollBack();
            Yii::error(['Exception while reviving thread', $exc->getMessage(), $exc->getTraceAsString()], 'podium');

            return PodiumResponse::error();
        }
    }

    /**
     * Executes after successful revive().
     */
    public function afterRevive(): void
    {
        $this->trigger(self::EVENT_AFTER_REVIVING, new ArchiveEvent(['model' => $this]));
    }
}
