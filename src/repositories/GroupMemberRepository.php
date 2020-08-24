<?php

declare(strict_types=1);

namespace bizley\podium\api\repositories;

use bizley\podium\api\ars\GroupMemberActiveRecord;
use bizley\podium\api\interfaces\GroupMemberRepositoryInterface;
use bizley\podium\api\interfaces\RepositoryInterface;
use LogicException;
use Throwable;
use yii\base\NotSupportedException;
use yii\db\StaleObjectException;

use function is_int;

final class GroupMemberRepository implements GroupMemberRepositoryInterface
{
    public string $activeRecordClass = GroupMemberActiveRecord::class;

    private ?GroupMemberActiveRecord $model = null;
    private array $errors = [];

    public function getModel(): GroupMemberActiveRecord
    {
        if (null === $this->model) {
            throw new LogicException('You need to call fetchOne() or setModel() first!');
        }

        return $this->model;
    }

    public function setModel(?GroupMemberActiveRecord $activeRecord): void
    {
        $this->model = $activeRecord;
    }

    /**
     * @throws NotSupportedException
     */
    public function getParent(): RepositoryInterface
    {
        throw new NotSupportedException('Group does not have parent!');
    }

    public function create($groupId, $memberId, array $data = []): bool
    {
        /** @var GroupMemberActiveRecord $group */
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

    public function fetchOne($groupId, $memberId): bool
    {
        $modelClass = $this->activeRecordClass;
        /** @var GroupMemberActiveRecord $modelClass */
        $model = $modelClass::findOne(
            [
                'group_id' => $groupId,
                'member_id' => $memberId,
            ]
        );
        if (null === $model) {
            return false;
        }
        $this->setModel($model);

        return true;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @throws Throwable
     * @throws StaleObjectException
     */
    public function delete(): bool
    {
        return is_int($this->getModel()->delete());
    }
}
