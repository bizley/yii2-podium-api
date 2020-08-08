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
    use ActiveRecordRepositoryTrait;

    public string $activeRecordClass = PostActiveRecord::class;

    private ?PostActiveRecord $model = null;

    public function getActiveRecordClass(): string
    {
        return $this->activeRecordClass;
    }

    public function getModel(): PostActiveRecord
    {
        if (null === $this->model) {
            throw new LogicException('You need to call fetchOne() or setModel() first!');
        }

        return $this->model;
    }

    public function setModel(?PostActiveRecord $activeRecord): void
    {
        $this->model = $activeRecord;
    }

    public function getId(): int
    {
        return $this->getModel()->id;
    }

    public function getParent(): RepositoryInterface
    {
        $threadRepository = $this->getModel()->thread;
        $parent = new ThreadRepository();
        $parent->setModel($threadRepository);

        return $parent;
    }

    public function delete(): bool
    {
        $post = $this->getModel();
        /** @var Transaction $transaction */
        $transaction = Yii::$app->db->beginTransaction();
        try {
            if (!$this->getParent()->updateCounters(0, -1)) {
                throw new Exception('Error while updating thread counters!');
            }
            if (false === $post->delete()) {
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
        return $this->getModel()->created_at;
    }
}
