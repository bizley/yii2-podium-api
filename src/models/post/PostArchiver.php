<?php

declare(strict_types=1);

namespace bizley\podium\api\models\post;

use bizley\podium\api\base\PodiumResponse;
use bizley\podium\api\events\ArchiveEvent;
use bizley\podium\api\interfaces\ArchiverInterface;
use bizley\podium\api\interfaces\ModelInterface;
use bizley\podium\api\models\thread\Thread;
use bizley\podium\api\models\thread\ThreadArchiver;
use bizley\podium\api\repos\PostRepo;
use Throwable;
use Yii;
use yii\db\Exception;

/**
 * Class PostArchiver
 * @package bizley\podium\api\models\post
 */
class PostArchiver extends PostRepo implements ArchiverInterface
{
    public const EVENT_BEFORE_ARCHIVING = 'podium.post.archiving.before';
    public const EVENT_AFTER_ARCHIVING = 'podium.post.archiving.after';
    public const EVENT_BEFORE_REVIVING = 'podium.post.reviving.before';
    public const EVENT_AFTER_REVIVING = 'podium.post.reviving.after';

    /**
     * @param int $modelId
     * @return ArchiverInterface|null
     */
    public static function findById(int $modelId): ?ArchiverInterface
    {
        return static::findOne(['id' => $modelId]);
    }

    /**
     * @return ModelInterface|null
     */
    public function getThreadModel(): ?ModelInterface
    {
        return Thread::findById($this->thread_id);
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
            $this->addError('archived', Yii::t('podium.error', 'post.already.archived'));
            return PodiumResponse::error($this);
        }

        $this->archived = true;

        if (!$this->validate()) {
            return PodiumResponse::error($this);
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            if (!$this->save(false)) {
                throw new Exception('Error while archiving post!');
            }

            $thread = $this->getThreadModel();
            if ($thread === null) {
                throw new Exception('Can not find parent thread!');
            }
            if (!$thread->updateCounters(['posts_count' => -1])) {
                throw new Exception('Error while updating thread counters!');
            }
            $forum = $thread->getParent();
            if ($forum === null) {
                throw new Exception('Can not find parent forum!');
            }
            if (!$forum->updateCounters(['posts_count' => -1])) {
                throw new Exception('Error while updating forum counters!');
            }
            if ($thread->getPostsCount() === 0 && !$thread->isArchived() && !$thread->convert(ThreadArchiver::class)->archive()) {
                throw new Exception('Error while archiving thread!');
            }

            $this->afterArchive();
            $transaction->commit();

            return PodiumResponse::success();

        } catch (Throwable $exc) {
            $transaction->rollBack();
            Yii::error(['Exception while archiving post', $exc->getMessage(), $exc->getTraceAsString()], 'podium');

            return PodiumResponse::error();
        }
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

        if (!$this->archived) {
            $this->addError('archived', Yii::t('podium.error', 'post.not.archived'));
            return PodiumResponse::error($this);
        }

        $this->archived = false;

        if (!$this->validate()) {
            return PodiumResponse::error($this);
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            if (!$this->save(false)) {
                throw new Exception('Error while reviving post!');
            }

            $thread = $this->getThreadModel();
            if (!$thread->updateCounters(['posts_count' => 1])) {
                throw new Exception('Error while updating thread counters!');
            }
            if (!$thread->getParent()->updateCounters(['posts_count' => 1])) {
                throw new Exception('Error while updating forum counters!');
            }

            $this->afterRevive();
            $transaction->commit();

            return PodiumResponse::success();

        } catch (Throwable $exc) {
            $transaction->rollBack();
            Yii::error(['Exception while reviving post', $exc->getMessage(), $exc->getTraceAsString()], 'podium');

            return PodiumResponse::error();
        }
    }

    public function afterRevive(): void
    {
        $this->trigger(self::EVENT_AFTER_REVIVING, new ArchiveEvent(['model' => $this]));
    }
}
