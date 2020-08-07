<?php

declare(strict_types=1);

namespace bizley\podium\api\repositories;

use bizley\podium\api\ars\CategoryActiveRecord;
use bizley\podium\api\interfaces\CategoryRepositoryInterface;
use bizley\podium\api\interfaces\RepositoryInterface;
use LogicException;
use Throwable;
use yii\base\NotSupportedException;
use yii\db\StaleObjectException;

use function is_int;

final class CategoryRepository implements CategoryRepositoryInterface
{
    public string $categoryActiveRecord = CategoryActiveRecord::class;

    private array $errors = [];
    private ?CategoryActiveRecord $model = null;

    public function find(int $id): bool
    {
        /** @var CategoryActiveRecord $modelClass */
        $modelClass = $this->categoryActiveRecord;
        /** @var CategoryActiveRecord|null $model */
        $model = $modelClass::findOne($id);
        if ($model === null) {
            return false;
        }
        $this->model = $model;
        return true;
    }

    public function setModel(CategoryActiveRecord $model): void
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

    /**
     * @return RepositoryInterface
     * @throws NotSupportedException
     */
    public function getParent(): RepositoryInterface
    {
        throw new NotSupportedException('Category has no parent!');
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
