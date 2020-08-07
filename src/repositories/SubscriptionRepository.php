<?php

declare(strict_types=1);

namespace bizley\podium\api\repositories;

use bizley\podium\api\ars\SubscriptionActiveRecord;
use bizley\podium\api\interfaces\SubscriptionRepositoryInterface;
use LogicException;
use Throwable;
use yii\db\StaleObjectException;

use function is_int;

final class SubscriptionRepository implements SubscriptionRepositoryInterface
{
    public string $subscriptionActiveRecord = SubscriptionActiveRecord::class;

    private array $errors = [];
    private ?SubscriptionActiveRecord $model = null;

    public function isMemberSubscribed(int $memberId, int $threadId): bool
    {
        /** @var SubscriptionActiveRecord $model */
        $model = $this->subscriptionActiveRecord;
        return $model::find()
            ->where(
                [
                    'member_id' => $memberId,
                    'thread_id' => $threadId,
                ]
            )
            ->exists();
    }

    public function subscribe(int $memberId, int $threadId): bool
    {
        /** @var SubscriptionActiveRecord $model */
        $model = new $this->subscriptionActiveRecord();
        $model->member_id = $memberId;
        $model->thread_id = $threadId;

        if (!$model->validate()) {
            $this->errors = $model->errors;
        }

        return $model->save(false);
    }

    public function find(int $memberId, int $threadId): bool
    {
        /** @var SubscriptionActiveRecord $modelClass */
        $modelClass = $this->subscriptionActiveRecord;
        /** @var SubscriptionActiveRecord|null $model */
        $model = $modelClass::find()
            ->where(
                [
                    'member_id' => $memberId,
                    'thread_id' => $threadId,
                ]
            )
            ->one();
        $this->model = $model;
        return $model === null;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @return bool
     * @throws Throwable
     * @throws StaleObjectException
     */
    public function delete(): bool
    {
        if ($this->model === null) {
            throw new LogicException('You need to call find() first!');
        }
        return is_int($this->model->delete());
    }
}
