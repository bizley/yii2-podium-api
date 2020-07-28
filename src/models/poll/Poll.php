<?php

declare(strict_types=1);

namespace bizley\podium\api\models\poll;

use bizley\podium\api\interfaces\ModelInterface;
use bizley\podium\api\interfaces\PollModelInterface;
use bizley\podium\api\models\thread\Thread;
use bizley\podium\api\repos\PollRepo;
use yii\base\InvalidArgumentException;
use yii\base\InvalidValueException;
use yii\base\NotSupportedException;
use yii\data\ActiveDataProvider;
use yii\data\DataFilter;
use yii\data\DataProviderInterface;
use yii\data\Pagination;
use yii\data\Sort;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

use function method_exists;

/**
 * Class Poll
 * @package bizley\podium\api\models\poll
 *
 * @property ModelInterface $parent
 * @property Thread $thread
 */
class Poll extends PollRepo implements PollModelInterface
{
    /**
     * @param int $modelId
     * @return PollModelInterface|null
     */
    public static function getByThreadId(int $modelId): ?PollModelInterface
    {
        return static::findOne(['thread_id' => $modelId]);
    }

    /**
     * @return ModelInterface|null
     */
    public function getParent(): ?ModelInterface
    {
        return $this->thread;
    }

    /**
     * @return ActiveQuery
     */
    public function getThread(): ActiveQuery
    {
        return $this->hasOne(Thread::class, ['id' => 'thread_id']);
    }

    /**
     * @return int
     * @throws NotSupportedException
     */
    public function getPostsCount(): int
    {
        throw new NotSupportedException('Poll has got no posts.');
    }

    /**
     * @return bool
     * @throws InvalidValueException
     */
    public function isArchived(): bool
    {
        $post = $this->getParent();
        if ($post === null) {
            throw new InvalidValueException('Floating poll detected!');
        }

        return $post->isArchived();
    }

    /**
     * @return string
     */
    public function getChoiceId(): string
    {
        return $this->choice_id;
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
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
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

        if (!method_exists($targetModel, 'tableName') || static::tableName() !== $targetModel::tableName()) {
            throw new InvalidArgumentException('You can only convert object extending the same repository.');
        }

        static::populateRecord($targetModel, $this->getOldAttributes());
        $targetModel->afterFind();

        return $targetModel;
    }
}
