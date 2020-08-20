<?php

declare(strict_types=1);

namespace bizley\podium\api\repositories;

use bizley\podium\api\ars\ForumActiveRecord;
use bizley\podium\api\interfaces\ForumRepositoryInterface;
use bizley\podium\api\interfaces\RepositoryInterface;
use LogicException;

final class ForumRepository implements ForumRepositoryInterface
{
    use ActiveRecordRepositoryTrait;

    public string $activeRecordClass = ForumActiveRecord::class;

    private ?ForumActiveRecord $model = null;

    public function getActiveRecordClass(): string
    {
        return $this->activeRecordClass;
    }

    public function getModel(): ForumActiveRecord
    {
        if (null === $this->model) {
            throw new LogicException('You need to call fetchOne() or setModel() first!');
        }

        return $this->model;
    }

    public function setModel(?ForumActiveRecord $activeRecord): void
    {
        $this->model = $activeRecord;
    }

    public function getId(): int
    {
        return $this->getModel()->id;
    }

    public function getParent(): RepositoryInterface
    {
        $category = $this->getModel()->category;
        $parent = new CategoryRepository();
        $parent->setModel($category);

        return $parent;
    }

    public function isArchived(): bool
    {
        return $this->getModel()->archived;
    }

    public function create(array $data, $authorId, $categoryId): bool
    {
        /** @var ForumActiveRecord $forum */
        $forum = new $this->activeRecordClass();
        if (!$forum->load($data, '')) {
            return false;
        }

        $forum->author_id = $authorId;
        $forum->category_id = $categoryId;

        if (!$forum->validate()) {
            $this->errors = $forum->errors;
            return false;
        }

        return $forum->save(false);
    }

    public function archive(): bool
    {
        $forum = $this->getModel();
        $forum->archived = true;
        if (!$forum->validate()) {
            $this->errors = $forum->errors;

            return false;
        }

        return $forum->save(false);
    }

    public function revive(): bool
    {
        $forum = $this->getModel();
        $forum->archived = false;
        if (!$forum->validate()) {
            $this->errors = $forum->errors;

            return false;
        }

        return $forum->save(false);
    }

    public function updateCounters(int $threads, int $posts): bool
    {
        return $this->getModel()->updateCounters(
            [
                'threads_count' => $threads,
                'posts_count' => $posts,
            ]
        );
    }

    public function setOrder(int $order): bool
    {
        $forum = $this->getModel();
        $forum->sort = $order;
        if (!$forum->validate()) {
            $this->errors = $forum->errors;

            return false;
        }

        return $forum->save(false);
    }

    public function getOrder(): int
    {
        return $this->getModel()->sort;
    }

    public function sort(): bool
    {
        /** @var ForumActiveRecord $forumClass */
        $forumClass = $this->activeRecordClass;
        $forums = $forumClass::find()
            ->orderBy(
                [
                    'sort' => SORT_ASC,
                    'name' => SORT_ASC,
                ]
            );
        $sortOrder = 0;
        /** @var ForumActiveRecord $forum */
        foreach ($forums->each() as $forum) {
            $forum->sort = $sortOrder++;
            if (!$forum->save()) {
                return false;
            }
        }

        return true;
    }

    public function move($categoryId): bool
    {
        $forum = $this->getModel();
        $forum->category_id = $categoryId;
        if (!$forum->validate()) {
            $this->errors = $forum->errors;
            return false;
        }

        return $forum->save(false);
    }
}
