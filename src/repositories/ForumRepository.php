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

    public function updateCounters(int $threads, int $posts): bool
    {
        return $this->getModel()->updateCounters(
            [
                'threads_count' => $threads,
                'posts_count' => $posts,
            ]
        );
    }
}
