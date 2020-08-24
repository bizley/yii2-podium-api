<?php

declare(strict_types=1);

namespace bizley\podium\api\repositories;

use bizley\podium\api\ars\GroupActiveRecord;
use bizley\podium\api\interfaces\GroupRepositoryInterface;
use bizley\podium\api\interfaces\RepositoryInterface;
use LogicException;
use yii\base\NotSupportedException;

final class GroupRepository implements GroupRepositoryInterface
{
    use ActiveRecordRepositoryTrait;

    public string $activeRecordClass = GroupActiveRecord::class;

    private ?GroupActiveRecord $model = null;

    public function getActiveRecordClass(): string
    {
        return $this->activeRecordClass;
    }

    public function getModel(): GroupActiveRecord
    {
        if (null === $this->model) {
            throw new LogicException('You need to call fetchOne() or setModel() first!');
        }

        return $this->model;
    }

    public function setModel(?GroupActiveRecord $activeRecord): void
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
        throw new NotSupportedException('Group does not have parent!');
    }

    public function create(array $data = []): bool
    {
        /** @var GroupActiveRecord $group */
        $group = new $this->activeRecordClass();
        if (!$group->load($data, '')) {
            return false;
        }

        if (!$group->validate()) {
            $this->errors = $group->errors;

            return false;
        }

        return $group->save(false);
    }
}
