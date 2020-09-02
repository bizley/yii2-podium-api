<?php

declare(strict_types=1);

namespace bizley\podium\api\repositories;

use bizley\podium\api\ars\MessageParticipantActiveRecord;
use bizley\podium\api\enums\MessageSide;
use bizley\podium\api\enums\MessageStatus;
use bizley\podium\api\interfaces\MemberRepositoryInterface;
use bizley\podium\api\interfaces\MessageParticipantRepositoryInterface;
use bizley\podium\api\interfaces\MessageRepositoryInterface;
use LogicException;
use Throwable;
use yii\base\NotSupportedException;
use yii\data\ActiveDataProvider;
use yii\data\DataFilter;
use yii\db\StaleObjectException;

use function is_int;

final class MessageParticipantRepository implements MessageParticipantRepositoryInterface
{
    public string $activeRecordClass = MessageParticipantActiveRecord::class;

    private ?MessageParticipantActiveRecord $model = null;
    private array $errors = [];
    private ?ActiveDataProvider $collection = null;

    public function getModel(): MessageParticipantActiveRecord
    {
        if (null === $this->model) {
            throw new LogicException('You need to call fetchOne() or setModel() first!');
        }

        return $this->model;
    }

    public function setModel(?MessageParticipantActiveRecord $activeRecord): void
    {
        $this->model = $activeRecord;
    }

    public function getCollection(): ?ActiveDataProvider
    {
        return $this->collection;
    }

    public function setCollection(?ActiveDataProvider $collection): void
    {
        $this->collection = $collection;
    }

    public function fetchOne($messageId, $memberId): bool
    {
        $modelClass = $this->activeRecordClass;
        /** @var MessageParticipantActiveRecord $modelClass */
        $model = $modelClass::findOne(
            [
                'message_id' => $messageId,
                'member_id' => $memberId,
            ]
        );
        if (null === $model) {
            return false;
        }
        $this->setModel($model);

        return true;
    }

    /**
     * @throws NotSupportedException
     */
    public function fetchAll($filter = null, $sort = null, $pagination = null): void
    {
        $modelClass = $this->activeRecordClass;
        /** @var MessageParticipantActiveRecord $modelClass */
        $query = $modelClass::find();
        if (null !== $filter) {
            if (!$filter instanceof DataFilter) {
                throw new NotSupportedException('Only filters implementing yii\data\DataFilter are supported!');
            }
            $filterConditions = $filter->build();
            if (false !== $filterConditions) {
                $query->andWhere($filterConditions);
            }
        }
        $dataProvider = new ActiveDataProvider(['query' => $query]);
        if (null !== $sort) {
            $dataProvider->setSort($sort);
        }
        if (null !== $pagination) {
            $dataProvider->setPagination($pagination);
        }
        $this->setCollection($dataProvider);
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

    public function edit(array $data = []): bool
    {
        $model = $this->getModel();
        if (!$model->load($data, '')) {
            return false;
        }

        if (!$model->validate()) {
            $this->errors = $model->errors;

            return false;
        }

        return $model->save(false);
    }

    public function getParent(): MessageRepositoryInterface
    {
        $message = $this->getModel()->message;
        $parent = new MessageRepository();
        $parent->setModel($message);

        return $parent;
    }

    public function isArchived(): bool
    {
        return $this->getModel()->archived;
    }

    public function archive(): bool
    {
        $messageSide = $this->getModel();
        $messageSide->archived = true;
        if (!$messageSide->validate()) {
            $this->errors = $messageSide->errors;

            return false;
        }

        return $messageSide->save(false);
    }

    public function revive(): bool
    {
        $messageSide = $this->getModel();
        $messageSide->archived = false;
        if (!$messageSide->validate()) {
            $this->errors = $messageSide->errors;

            return false;
        }

        return $messageSide->save(false);
    }

    public function copy(
        MessageRepositoryInterface $message,
        MemberRepositoryInterface $member,
        $sideId,
        array $data = []
    ): bool {
        /** @var MessageParticipantActiveRecord $model */
        $model = new $this->activeRecordClass();
        if (!$model->load($data, '')) {
            return false;
        }

        $model->message_id = $message->getId();
        $model->member_id = $member->getId();
        $model->archived = false;
        $model->side_id = $sideId;
        $model->status_id = MessageSide::SENDER === $sideId ? MessageStatus::READ : MessageStatus::NEW;

        if (!$model->validate()) {
            $this->errors = $model->errors;

            return false;
        }

        return $model->save(false);
    }
}
