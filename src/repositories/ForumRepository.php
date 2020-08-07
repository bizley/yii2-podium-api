<?php

declare(strict_types=1);

namespace bizley\podium\api\repositories;

use bizley\podium\api\ars\ForumActiveRecord;
use bizley\podium\api\interfaces\ForumRepositoryInterface;
use bizley\podium\api\interfaces\RepositoryInterface;
use LogicException;
use Throwable;
use yii\db\StaleObjectException;

use function is_int;

final class ForumRepository implements ForumRepositoryInterface
{
    public string $forumActiveRecord = ForumActiveRecord::class;

    private array $errors = [];
    private ?ForumActiveRecord $model = null;

    public function find(int $id): bool
    {
        /** @var ForumActiveRecord $modelClass */
        $modelClass = $this->forumActiveRecord;
        /** @var ForumActiveRecord|null $model */
        $model = $modelClass::findOne($id);
        if ($model === null) {
            return false;
        }
        $this->model = $model;
        return true;
    }

    public function setModel(ForumActiveRecord $model): void
    {
        $this->model = $model;
    }

    public function getErrors(): array
    {
        return $this->errors;
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
        $parent = new CategoryRepository();
        $parent->setModel($this->model->category);
        return $parent;
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

    public function updateCounters(int $threads, int $posts): bool
    {
        if ($this->model === null) {
            throw new LogicException('You need to call find() first!');
        }
        return $this->model->updateCounters(
            [
                'threads_count' => $threads,
                'posts_count' => $posts,
            ]
        );
    }
}
