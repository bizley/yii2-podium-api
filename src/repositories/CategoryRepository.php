<?php

declare(strict_types=1);

namespace bizley\podium\api\repositories;

use bizley\podium\api\ars\CategoryActiveRecord;
use bizley\podium\api\interfaces\ActiveRecordRepositoryInterface;
use bizley\podium\api\interfaces\CategoryRepositoryInterface;
use bizley\podium\api\interfaces\MemberRepositoryInterface;
use bizley\podium\api\interfaces\RepositoryInterface;
use DomainException;
use LogicException;
use yii\base\NotSupportedException;
use yii\db\ActiveRecord;

use function is_int;

use const SORT_ASC;
use const SORT_DESC;

final class CategoryRepository implements CategoryRepositoryInterface, ActiveRecordRepositoryInterface
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

    public function setModel(ActiveRecord $categoryActiveRecord): void
    {
        if (!$categoryActiveRecord instanceof CategoryActiveRecord) {
            throw new LogicException('You need to pass bizley\podium\api\ars\CategoryActiveRecord!');
        }

        $this->model = $categoryActiveRecord;
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

    public function create(MemberRepositoryInterface $author, array $data = []): bool
    {
        $authorId = $author->getId();
        if (!is_int($authorId)) {
            throw new DomainException('Invalid author ID!');
        }

        /** @var CategoryActiveRecord $category */
        $category = new $this->activeRecordClass();
        if (!$category->load($data, '')) {
            return false;
        }

        if (null === $category->sort) {
            /** @var CategoryActiveRecord $categoryClass */
            $categoryClass = $this->activeRecordClass;
            /** @var CategoryActiveRecord|null $lastCategory */
            $lastCategory = $categoryClass::find()
                ->orderBy(
                    [
                        'sort' => SORT_DESC,
                        'name' => SORT_DESC,
                    ]
                )
                ->limit(1)
                ->one();
            if ($lastCategory) {
                $category->sort = $lastCategory->sort + 1;
            } else {
                $category->sort = 0;
            }
        }

        $category->author_id = $authorId;

        if (!$category->save()) {
            $this->errors = $category->errors;

            return false;
        }

        $this->setModel($category);

        return true;
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

    public function sort(): bool
    {
        /** @var CategoryActiveRecord $categoryClass */
        $categoryClass = $this->activeRecordClass;
        $categories = $categoryClass::find()
            ->orderBy(
                [
                    'sort' => SORT_ASC,
                    'name' => SORT_ASC,
                ]
            );
        $sortOrder = 0;
        /** @var CategoryActiveRecord $category */
        foreach ($categories->each() as $category) {
            $category->sort = $sortOrder++;
            if (!$category->save()) {
                return false;
            }
        }

        return true;
    }
}
