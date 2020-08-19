<?php

declare(strict_types=1);

namespace bizley\podium\api\repositories;

use bizley\podium\api\ars\CategoryActiveRecord;
use bizley\podium\api\interfaces\CategoryRepositoryInterface;
use bizley\podium\api\interfaces\RepositoryInterface;
use LogicException;
use yii\base\NotSupportedException;

use const SORT_DESC;

final class CategoryRepository implements CategoryRepositoryInterface
{
    use ActiveRecordRepositoryTrait;

    public string $activeRecordClass = CategoryActiveRecord::class;

    private ?CategoryActiveRecord $model = null;

    public function getActiveRecordClass(): string
    {
        return $this->activeRecordClass;
    }

    public function getModel(): CategoryActiveRecord
    {
        if (null === $this->model) {
            throw new LogicException('You need to call fetchOne() or setModel() first!');
        }

        return $this->model;
    }

    public function setModel(?CategoryActiveRecord $activeRecord): void
    {
        $this->model = $activeRecord;
    }

    public function getId(): int
    {
        return $this->getModel()->id;
    }

    /**
     * @throws NotSupportedException
     */
    public function getParent(): RepositoryInterface
    {
        throw new NotSupportedException('Category has no parent!');
    }

    public function create(array $data, $authorId): bool
    {
        /** @var CategoryActiveRecord $category */
        $category = new $this->activeRecordClass();
        if (!$category->load($data, '')) {
            return false;
        }

        if (null === $category->sort) {
            /** @var CategoryActiveRecord $categoryClass */
            $categoryClass = $this->activeRecordClass;
            /** @var CategoryActiveRecord $lastCategory */
            $lastCategory = $categoryClass::find()->orderBy(['sort' => SORT_DESC])->limit(1)->one();
            if ($lastCategory) {
                $category->sort = $lastCategory->sort + 1;
            } else {
                $category->sort = 0;
            }
        }

        $category->author_id = $authorId;

        if (!$category->validate()) {
            $this->errors = $category->errors;

            return false;
        }

        return $category->save(false);
    }

    public function isArchived(): bool
    {
        return $this->getModel()->archived;
    }

    public function archive(): bool
    {
        $category = $this->getModel();
        $category->archived = true;
        if (!$category->validate()) {
            $this->errors = $category->errors;

            return false;
        }

        return $category->save(false);
    }

    public function revive(): bool
    {
        $category = $this->getModel();
        $category->archived = false;
        if (!$category->validate()) {
            $this->errors = $category->errors;

            return false;
        }

        return $category->save(false);
    }

    public function setOrder(int $order): bool
    {
        $category = $this->getModel();
        $category->sort = $order;
        if (!$category->validate()) {
            $this->errors = $category->errors;

            return false;
        }

        return $category->save(false);
    }

    public function getOrder(): int
    {
        return $this->getModel()->sort;
    }
}
