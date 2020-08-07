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
use yii\db\StaleObjectException;
use yii\db\Transaction;

use function is_int;

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

    public function isArchived(): bool
    {
        if ($this->model === null) {
            throw new LogicException('You need to call find() first!');
        }
        return $this->model->archived;
    }

    /**
     * @return bool
     * @throws StaleObjectException
     * @throws Throwable
     */
    public function delete(): bool
    {
        if ($this->model === null) {
            throw new LogicException('You need to call find() first!');
        }
        return is_int($this->model->delete());
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

    public function getErrors(): array
    {
        return $this->errors;
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
}
