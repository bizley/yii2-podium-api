<?php

declare(strict_types=1);

namespace bizley\podium\api\repositories;

use bizley\podium\api\ars\RankActiveRecord;
use bizley\podium\api\interfaces\ActiveRecordRankRepositoryInterface;
use bizley\podium\api\interfaces\RepositoryInterface;
use LogicException;

final class RankRepository implements ActiveRecordRankRepositoryInterface
{
    use ActiveRecordRepositoryTrait;

    public string $activeRecordClass = RankActiveRecord::class;

    private ?RankActiveRecord $model = null;

    public function getActiveRecordClass(): string
    {
        return $this->activeRecordClass;
    }

    public function getModel(): RankActiveRecord
    {
        if (null === $this->model) {
            throw new LogicException('You need to call fetchOne() or setModel() first!');
        }

        return $this->model;
    }

    public function setModel(?RankActiveRecord $activeRecord): void
    {
        $this->model = $activeRecord;
    }

    public function getId(): int
    {
        return $this->getModel()->id;
    }

    public function getParent(): RepositoryInterface
    {
        $forumRepository = $this->getModel()->forum;
        $parent = new ForumRepository();
        $parent->setModel($forumRepository);

        return $parent;
    }
}
