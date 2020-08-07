<?php

declare(strict_types=1);

namespace bizley\podium\api\repositories;

use bizley\podium\api\ars\ThreadActiveRecord;
use bizley\podium\api\interfaces\ForumRepositoryInterface;
use bizley\podium\api\interfaces\RepositoryInterface;
use bizley\podium\api\interfaces\ThreadRepositoryInterface;
use LogicException;
use Throwable;
use Yii;
use yii\db\Exception;
use yii\db\Transaction;

final class ThreadRepository implements ThreadRepositoryInterface
{
    public string $threadActiveRecord = ThreadActiveRecord::class;

    private array $errors = [];
    private ?ThreadActiveRecord $model = null;

    public function find(int $id): bool
    {
        /** @var ThreadActiveRecord $modelClass */
        $modelClass = $this->threadActiveRecord;
        /** @var ThreadActiveRecord|null $model */
        $model = $modelClass::findOne($id);
        $this->model = $model;
        return $model === null;
    }

    public function getId(): int
    {
        if ($this->model === null) {
            throw new LogicException('You need to call find() first!');
        }
        return $this->model->id;
    }

    public function getParent(): RepositoryInterface
    {
        if ($this->model === null) {
            throw new LogicException('You need to call find() first!');
        }
        $parent = new ForumRepository();
        $parent->setModel($this->model->forum);
        return $parent;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function isArchived(): bool
    {
        if ($this->model === null) {
            throw new LogicException('You need to call find() first!');
        }
        return $this->model->archived;
    }

    public function delete(): bool
    {
        if ($this->model === null) {
            throw new LogicException('You need to call find() first!');
        }

        /** @var Transaction $transaction */
        $transaction = Yii::$app->db->beginTransaction();
        try {
            if (!$this->getParent()->updateCounters(-1, -$this->model->posts_count)) {
                throw new Exception('Error while updating forum counters!');
            }
            if ($this->model->delete() === false) {
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
        if ($this->model === null) {
            throw new LogicException('You need to call find() first!');
        }

        $this->model->pinned = true;

        if (!$this->model->validate()) {
            $this->errors = $this->model->errors;
        }

        return $this->model->save(false);
    }

    public function unpin(): bool
    {
        if ($this->model === null) {
            throw new LogicException('You need to call find() first!');
        }

        $this->model->pinned = false;

        if (!$this->model->validate()) {
            $this->errors = $this->model->errors;
        }

        return $this->model->save(false);
    }

    public function move(ForumRepositoryInterface $newForum): bool
    {
        if ($this->model === null) {
            throw new LogicException('You need to call find() first!');
        }

        $oldForum = $this->getParent();

        $this->model->forum_id = $newForum->getId();
        $this->model->category_id = $newForum->getParent()->getId();

        /** @var Transaction $transaction */
        $transaction = Yii::$app->db->beginTransaction();
        try {
            if (!$this->model->save()) {
                $this->errors = $this->model->errors;
                return false;
            }
            if (!$oldForum->updateCounters(-1, -$this->model->posts_count)) {
                throw new Exception('Error while updating old forum counters!');
            }
            if (!$newForum->updateCounters(1, $this->model->posts_count)) {
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
        if ($this->model === null) {
            throw new LogicException('You need to call find() first!');
        }

        $this->model->locked = true;

        if (!$this->model->validate()) {
            $this->errors = $this->model->errors;
        }

        return $this->model->save(false);
    }

    public function unlock(): bool
    {
        if ($this->model === null) {
            throw new LogicException('You need to call find() first!');
        }

        $this->model->locked = false;

        if (!$this->model->validate()) {
            $this->errors = $this->model->errors;
        }

        return $this->model->save(false);
    }

    public function archive(): bool
    {
        if ($this->model === null) {
            throw new LogicException('You need to call find() first!');
        }

        $this->model->archived = true;

        if (!$this->model->validate()) {
            $this->errors = $this->model->errors;
        }

        return $this->model->save(false);
    }

    public function revive(): bool
    {
        if ($this->model === null) {
            throw new LogicException('You need to call find() first!');
        }

        $this->model->archived = false;

        if (!$this->model->validate()) {
            $this->errors = $this->model->errors;
        }

        return $this->model->save(false);
    }
}
