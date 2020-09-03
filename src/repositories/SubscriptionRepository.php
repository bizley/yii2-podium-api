<?php

declare(strict_types=1);

namespace bizley\podium\api\repositories;

use bizley\podium\api\ars\SubscriptionActiveRecord;
use bizley\podium\api\interfaces\MemberRepositoryInterface;
use bizley\podium\api\interfaces\SubscriptionRepositoryInterface;
use bizley\podium\api\interfaces\ThreadRepositoryInterface;
use LogicException;
use Throwable;
use yii\db\StaleObjectException;

final class SubscriptionRepository implements SubscriptionRepositoryInterface
{
    public string $activeRecordClass = SubscriptionActiveRecord::class;

    private ?SubscriptionActiveRecord $model = null;
    private array $errors = [];

    public function getModel(): SubscriptionActiveRecord
    {
        if (null === $this->model) {
            throw new LogicException('You need to call fetchOne() or setModel() first!');
        }

        return $this->model;
    }

    public function setModel(?SubscriptionActiveRecord $activeRecord): void
    {
        $this->model = $activeRecord;
    }

    public function isMemberSubscribed(MemberRepositoryInterface $member, ThreadRepositoryInterface $thread): bool
    {
        $modelClass = $this->activeRecordClass;
        /* @var SubscriptionActiveRecord $modelClass */
        return $modelClass::find()
            ->where(
                [
                    'member_id' => $member->getId(),
                    'thread_id' => $thread->getId(),
                ]
            )
            ->exists();
    }

    public function subscribe(MemberRepositoryInterface $member, ThreadRepositoryInterface $thread): bool
    {
        /** @var SubscriptionActiveRecord $model */
        $model = new $this->activeRecordClass();
        $model->member_id = $member->getId();
        $model->thread_id = $thread->getId();

        if (!$model->validate()) {
            $this->errors = $model->errors;
            return false;
        }

        return $model->save(false);
    }

    public function fetchOne(MemberRepositoryInterface $member, ThreadRepositoryInterface $thread): bool
    {
        /** @var SubscriptionActiveRecord $modelClass */
        $modelClass = $this->activeRecordClass;
        /** @var SubscriptionActiveRecord|null $model */
        $model = $modelClass::find()
            ->where(
                [
                    'member_id' => $member->getId(),
                    'thread_id' => $thread->getId(),
                ]
            )
            ->one();
        if (null === $model) {
            return false;
        }
        $this->model = $model;

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
