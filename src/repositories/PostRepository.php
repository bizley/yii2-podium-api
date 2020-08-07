<?php

declare(strict_types=1);

namespace bizley\podium\api\repositories;

use bizley\podium\api\ars\PostActiveRecord;
use bizley\podium\api\interfaces\PostRepositoryInterface;
use bizley\podium\api\interfaces\RepositoryInterface;
use LogicException;
use Throwable;
use Yii;
use yii\db\Exception;
use yii\db\Transaction;

final class PostRepository implements PostRepositoryInterface
{
    public string $postActiveRecord = PostActiveRecord::class;

    private array $errors = [];
    private ?PostActiveRecord $model = null;

    public function find(int $id): bool
    {
        /** @var PostActiveRecord $modelClass */
        $modelClass = $this->postActiveRecord;
        /** @var PostActiveRecord|null $model */
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
        $parent = new ThreadRepository();
        $parent->setModel($this->model->thread);
        return $parent;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function delete(): bool
    {
        if ($this->model === null) {
            throw new LogicException('You need to call find() first!');
        }

        /** @var Transaction $transaction */
        $transaction = Yii::$app->db->beginTransaction();
        try {
            if (!$this->getParent()->updateCounters(0, -1)) {
                throw new Exception('Error while updating thread counters!');
            }
            if ($this->model->delete() === false) {
                throw new Exception('Error while deleting post!');
            }
            $transaction->commit();
            return true;
        } catch (Throwable $exc) {
            $transaction->rollBack();
            Yii::error(['Exception while deleting post', $exc->getMessage(), $exc->getTraceAsString()], 'podium');
        }

        return false;
    }

    public function getCreatedAt(): int
    {
        if ($this->model === null) {
            throw new LogicException('You need to call find() first!');
        }
        return $this->model->created_at;
    }
}
