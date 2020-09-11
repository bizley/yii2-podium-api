<?php

declare(strict_types=1);

namespace bizley\podium\api\repositories;

use bizley\podium\api\ars\GroupMemberActiveRecord;
use bizley\podium\api\interfaces\GroupMemberRepositoryInterface;
use bizley\podium\api\interfaces\GroupRepositoryInterface;
use bizley\podium\api\interfaces\MemberRepositoryInterface;
use bizley\podium\api\interfaces\RepositoryInterface;
use DomainException;
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

    public function create(GroupRepositoryInterface $group, MemberRepositoryInterface $member, array $data = []): bool
    {
        $groupId = $group->getId();
        if (!is_int($groupId)) {
            throw new DomainException('Invalid group ID!');
        }
        $memberId = $member->getId();
        if (!is_int($memberId)) {
            throw new DomainException('Invalid member ID!');
        }

        /** @var GroupMemberActiveRecord $groupMember */
        $groupMember = new $this->activeRecordClass();
        if (!$groupMember->load($data, '')) {
            return false;
        }

        $groupMember->group_id = $groupId;
        $groupMember->member_id = $memberId;

        if (!$groupMember->save()) {
            $this->errors = $groupMember->errors;

            return false;
        }

        $this->setModel($groupMember);

        return true;
    }

    public function fetchOne(GroupRepositoryInterface $group, MemberRepositoryInterface $member): bool
    {
        $groupId = $group->getId();
        if (!is_int($groupId)) {
            throw new DomainException('Invalid group ID!');
        }
        $memberId = $member->getId();
        if (!is_int($memberId)) {
            throw new DomainException('Invalid member ID!');
        }

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
