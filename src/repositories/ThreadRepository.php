<?php

declare(strict_types=1);

namespace bizley\podium\api\repositories;

use bizley\podium\api\ars\ThreadActiveRecord;
use bizley\podium\api\interfaces\ThreadRepositoryInterface;
use LogicException;
use Throwable;
use yii\db\StaleObjectException;

use function is_int;

class ThreadRepository implements ThreadRepositoryInterface
{
    public string $threadActiveRecord = ThreadActiveRecord::class;

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
}
