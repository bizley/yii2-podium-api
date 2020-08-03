<?php

declare(strict_types=1);

namespace bizley\podium\api\models\message;

use bizley\podium\api\interfaces\MessageParticipantModelInterface;
use bizley\podium\api\interfaces\ModelInterface;
use bizley\podium\api\repos\MessageParticipantRepo;
use yii\base\InvalidArgumentException;
use yii\base\NotSupportedException;
use yii\data\ActiveDataProvider;
use yii\data\DataFilter;
use yii\data\DataProviderInterface;
use yii\data\Pagination;
use yii\data\Sort;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * Class Message
 * @package bizley\podium\api\models\message
 *
 * @property ModelInterface $parent
 * @property Message $message
 */
class MessageParticipant extends MessageParticipantRepo implements MessageParticipantModelInterface
{
    /**
     * @return ModelInterface|null
     */
    public function getParent(): ?ModelInterface
    {
        return $this->message;
    }

    /**
     * @return ActiveQuery
     */
    public function getMessage(): ActiveQuery
    {
        return $this->hasOne(Message::class, ['id' => 'message_id']);
    }

    /**
     * @return int
     * @throws NotSupportedException
     */
    public function getPostsCount(): int
    {
        throw new NotSupportedException('Post has got no posts.');
    }

    /**
     * @return bool
     */
    public function isArchived(): bool
    {
        return (bool)$this->archived;
    }

    /**
     * @return int
     */
    public function getMemberId(): int
    {
        return $this->member_id;
    }

    /**
     * @return string
     */
    public function getSideId(): string
    {
        return $this->side_id;
    }

    /**
     * @return int
     * @throws NotSupportedException
     */
    public function getId(): int
    {
        throw new NotSupportedException('Message Participant has got no ID.');
    }

    /**
     * @param int $modelId
     * @return ModelInterface|null
     */
    public static function findById(int $modelId): ?ModelInterface
    {
        return static::findOne(['id' => $modelId]);
    }

    /**
     * @return int
     */
    public function getCreatedAt(): int
    {
        return $this->created_at;
    }

    /**
     * @param DataFilter|null $filter
     * @param Sort|array|bool|null $sort
     * @param Pagination|array|bool|null $pagination
     * @return ActiveDataProvider
     */
    public static function findByFilter(
        DataFilter $filter = null,
        $sort = null,
        $pagination = null
    ): DataProviderInterface {
        $query = static::find();

        if ($filter !== null) {
            $filterConditions = $filter->build();
            if ($filterConditions !== false) {
                $query->andWhere($filterConditions);
            }
        }

        $dataProvider = new ActiveDataProvider(['query' => $query]);

        if ($sort !== null) {
            $dataProvider->setSort($sort);
        }
        if ($pagination !== null) {
            $dataProvider->setPagination($pagination);
        }

        return $dataProvider;
    }

    /**
     * @param string $targetClass
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function convert(string $targetClass)
    {
        /** @var ActiveRecord $targetModel */
        $targetModel = new $targetClass();

        if (static::tableName() !== $targetModel::tableName()) {
            throw new InvalidArgumentException('You can only convert object extending the same repository.');
        }

        static::populateRecord($targetModel, $this->getOldAttributes());
        $targetModel->afterFind();

        return $targetModel;
    }
}
