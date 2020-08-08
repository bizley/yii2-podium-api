<?php

declare(strict_types=1);

namespace bizley\podium\api\repositories;

use Throwable;
use Yii;
use yii\base\NotSupportedException;
use yii\data\ActiveDataProvider;
use yii\data\DataFilter;
use yii\db\ActiveRecord;
use yii\db\Exception;

trait ActiveRecordRepositoryTrait
{
    private array $errors = [];
    private ?ActiveDataProvider $collection = null;

    abstract public function getActiveRecordClass(): string;

    abstract public function getModel(): ActiveRecord;

    abstract public function setModel(ActiveRecord $model): void;

    public function getCollection(): ?ActiveDataProvider
    {
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

    public function fetchOne(int $id): bool
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

    public function delete(): bool
    {
        try {
            if (false === $this->getModel()->delete()) {
                throw new Exception('Error while deleting model!');
            }

            return true;
        } catch (Throwable $exc) {
            Yii::error(['Exception while deleting thread', $exc->getMessage(), $exc->getTraceAsString()], 'podium');
        }

        return false;
    }
}
