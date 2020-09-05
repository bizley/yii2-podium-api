<?php

declare(strict_types=1);

namespace bizley\podium\api\repositories;

use LogicException;
use Throwable;
use yii\base\NotSupportedException;
use yii\data\ActiveDataProvider;
use yii\data\DataFilter;
use yii\db\ActiveRecord;
use yii\db\StaleObjectException;

use function is_int;

trait ActiveRecordRepositoryTrait
{
    private array $errors = [];
    private ?ActiveDataProvider $collection = null;

    abstract public function getActiveRecordClass(): string;

    abstract public function getModel(): ActiveRecord;

    abstract public function setModel(ActiveRecord $model): void;

    public function getCollection(): ActiveDataProvider
    {
        if ($this->collection === null) {
            throw new LogicException('You need to call fetchAll() first!');
        }

        return $this->collection;
    }

    public function setCollection(?ActiveDataProvider $collection): void
    {
        $this->collection = $collection;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @param int|string|array $id
     */
    public function fetchOne($id): bool
    {
        $modelClass = $this->getActiveRecordClass();
        /** @var ActiveRecord $modelClass */
        $model = $modelClass::findOne($id);
        if (null === $model) {
            return false;
        }
        $this->setModel($model);

        return true;
    }

    /**
     * @param mixed|null $filter
     * @param mixed|null $sort
     * @param mixed|null $pagination
     *
     * @throws NotSupportedException
     */
    public function fetchAll($filter = null, $sort = null, $pagination = null): void
    {
        $modelClass = $this->getActiveRecordClass();
        /** @var ActiveRecord $modelClass */
        $query = $modelClass::find();
        if (null !== $filter) {
            if (!$filter instanceof DataFilter) {
                throw new NotSupportedException('Only filters implementing yii\data\DataFilter are supported!');
            }
            $filterConditions = $filter->build();
            if (false !== $filterConditions) {
                $query->andWhere($filterConditions);
            }
        }
        $dataProvider = new ActiveDataProvider(['query' => $query]);
        if (null !== $sort) {
            $dataProvider->setSort($sort);
        }
        if (null !== $pagination) {
            $dataProvider->setPagination($pagination);
        }
        $this->setCollection($dataProvider);
    }

    /**
     * @throws Throwable
     * @throws StaleObjectException
     */
    public function delete(): bool
    {
        return is_int($this->getModel()->delete());
    }

    public function edit(array $data = []): bool
    {
        $model = $this->getModel();
        if (!$model->load($data, '')) {
            return false;
        }

        if (!$model->validate()) {
            $this->errors = $model->errors;

            return false;
        }

        return $model->save(false);
    }
}
