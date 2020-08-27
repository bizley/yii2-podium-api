<?php

declare(strict_types=1);

namespace bizley\podium\api\repositories;

use bizley\podium\api\ars\MemberActiveRecord;
use bizley\podium\api\enums\MemberStatus;
use bizley\podium\api\interfaces\MemberRepositoryInterface;
use bizley\podium\api\interfaces\RepositoryInterface;
use LogicException;
use yii\base\NotSupportedException;

final class MemberRepository implements MemberRepositoryInterface
{
    use ActiveRecordRepositoryTrait;

    public string $activeRecordClass = MemberActiveRecord::class;

    private ?MemberActiveRecord $model = null;

    public function getActiveRecordClass(): string
    {
        return $this->activeRecordClass;
    }

    public function getModel(): MemberActiveRecord
    {
        if (null === $this->model) {
            throw new LogicException('You need to call fetchOne() or setModel() first!');
        }

        return $this->model;
    }

    public function setModel(?MemberActiveRecord $activeRecord): void
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
        throw new NotSupportedException('Member does not have parent!');
    }

    public function register($id, array $data = []): bool
    {
        /** @var MemberActiveRecord $member */
        $member = new $this->activeRecordClass();
        if (!$member->load($data, '')) {
            return false;
        }

        $member->user_id = $id; // TODO composite id handling
        $member->status_id = MemberStatus::REGISTERED;

        if (!$member->validate()) {
            $this->errors = $member->errors;
            return false;
        }

        return $member->save(false);
    }

    public function activate(): bool
    {
        $member = $this->getModel();
        $member->status_id = MemberStatus::ACTIVE;
        if (!$member->validate()) {
            $this->errors = $member->errors;

            return false;
        }

        return $member->save(false);
    }

    public function ban(): bool
    {
        $member = $this->getModel();
        $member->status_id = MemberStatus::BANNED;
        if (!$member->validate()) {
            $this->errors = $member->errors;

            return false;
        }

        return $member->save(false);
    }

    public function unban(): bool
    {
        $member = $this->getModel();
        $member->status_id = MemberStatus::ACTIVE;
        if (!$member->validate()) {
            $this->errors = $member->errors;

            return false;
        }

        return $member->save(false);
    }
}
