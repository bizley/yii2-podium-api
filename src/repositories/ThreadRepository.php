<?php

declare(strict_types=1);

namespace bizley\podium\api\repositories;

use bizley\podium\api\ars\ThreadActiveRecord;
use bizley\podium\api\interfaces\ActiveRecordThreadRepositoryInterface;
use bizley\podium\api\interfaces\ForumRepositoryInterface;
use bizley\podium\api\interfaces\RepositoryInterface;
use LogicException;
use Throwable;
use Yii;
use yii\db\Exception;
use yii\db\Transaction;

final class ThreadRepository implements ActiveRecordThreadRepositoryInterface
{
    use ActiveRecordRepositoryTrait;

    public string $activeRecordClass = ThreadActiveRecord::class;

    private ?ThreadActiveRecord $model = null;

    public function getActiveRecordClass(): string
    {
        return $this->activeRecordClass;
    }

    public function getModel(): ThreadActiveRecord
    {
        if (null === $this->model) {
            throw new LogicException('You need to call fetchOne() or setModel() first!');
        }
        return $this->model;
    }

    public function setModel(?ThreadActiveRecord $activeRecord): void
    {
        $this->model = $activeRecord;
    }

    public function getId(): int
    {
        return $this->getModel()->id;
    }

    public function getParent(): RepositoryInterface
    {
        $forumRepository = $this->getModel()->forum;
        $parent = new ForumRepository();
        $parent->setModel($forumRepository);

        return $parent;
    }

    public function isArchived(): bool
    {
        return $this->getModel()->archived;
    }

    public function delete(): bool
    {
        $thread = $this->getModel();
        /** @var Transaction $transaction */
        $transaction = Yii::$app->db->beginTransaction();
        try {
            if (!$this->getParent()->updateCounters(-1, -$thread->posts_count)) {
                throw new Exception('Error while updating forum counters!');
            }
            if (false === $thread->delete()) {
                throw new Exception('Error while deleting thread!');
            }
            $transaction->commit();

            return true;
        } catch (Throwable $exc) {
            $transaction->rollBack();
            Yii::error(['Exception while deleting thread', $exc->getMessage(), $exc->getTraceAsString()], 'podium');
        }

        return false;
    }

    public function pin(): bool
    {
        $thread = $this->getModel();
        $thread->pinned = true;
        if (!$thread->validate()) {
            $this->errors = $thread->errors;
        }

        return $thread->save(false);
    }

    public function unpin(): bool
    {
        $thread = $this->getModel();
        $thread->pinned = false;
        if (!$thread->validate()) {
            $this->errors = $thread->errors;
        }

        return $thread->save(false);
    }

    public function move(ForumRepositoryInterface $newForum): bool
    {
        $thread = $this->getModel();
        $oldForum = $this->getParent();
        $thread->forum_id = $newForum->getId();
        $thread->category_id = $newForum->getParent()->getId();
        /** @var Transaction $transaction */
        $transaction = Yii::$app->db->beginTransaction();
        try {
            if (!$thread->save()) {
                $this->errors = $thread->errors;

                return false;
            }
            if (!$oldForum->updateCounters(-1, -$thread->posts_count)) {
                throw new Exception('Error while updating old forum counters!');
            }
            if (!$newForum->updateCounters(1, $thread->posts_count)) {
                throw new Exception('Error while updating new forum counters!');
            }
            $transaction->commit();

            return true;
        } catch (Throwable $exc) {
            $transaction->rollBack();
            Yii::error(['Exception while moving thread', $exc->getMessage(), $exc->getTraceAsString()], 'podium');
        }

        return false;
    }

    public function lock(): bool
    {
        $thread = $this->getModel();
        $thread->locked = true;
        if (!$thread->validate()) {
            $this->errors = $thread->errors;
        }

        return $thread->save(false);
    }

    public function unlock(): bool
    {
        $thread = $this->getModel();
        $thread->locked = false;
        if (!$thread->validate()) {
            $this->errors = $thread->errors;
        }

        return $thread->save(false);
    }

    public function archive(): bool
    {
        $thread = $this->getModel();
        $thread->archived = true;
        if (!$thread->validate()) {
            $this->errors = $thread->errors;
        }

        return $thread->save(false);
    }

    public function revive(): bool
    {
        $thread = $this->getModel();
        $thread->archived = false;
        if (!$thread->validate()) {
            $this->errors = $thread->errors;
        }

        return $thread->save(false);
    }
}
