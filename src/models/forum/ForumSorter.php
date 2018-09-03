<?php

declare(strict_types=1);

namespace bizley\podium\api\models\forum;

use bizley\podium\api\base\PodiumResponse;
use bizley\podium\api\events\SortEvent;
use bizley\podium\api\interfaces\ModelInterface;
use bizley\podium\api\interfaces\SortableInterface;
use bizley\podium\api\repos\ForumRepo;
use Yii;
use yii\base\NotSupportedException;

/**
 * Class ForumSorter
 * @package bizley\podium\api\models\forum
 */
class ForumSorter extends ForumRepo implements SortableInterface
{
    public const EVENT_BEFORE_SORTING = 'podium.forum.sorting.before';
    public const EVENT_AFTER_SORTING = 'podium.forum.sorting.after';

    public $sortOrder;

    /**
     * @param ModelInterface $category
     */
    public function setCategory(ModelInterface $category): void
    {
        $this->category_id = $category->getId();
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            [['sortOrder'], 'required'],
            [['sortOrder'], 'each', 'rule' => ['integer']],
            [['sortOrder'], 'each', 'rule' => ['exist', 'targetAttribute' => 'id']],
        ];
    }

    /**
     * @return array
     */
    public function attributeLabels(): array
    {
        return [
            'sortOrder' => Yii::t('podium.label', 'forum.sort.order'),
        ];
    }

    /**
     * @param array $data
     * @return bool
     */
    public function loadData(array $data = []): bool
    {
        return $this->load(['sortOrder' => $data], '');
    }

    /**
     * @return bool
     */
    public function beforeSort(): bool
    {
        $event = new SortEvent();
        $this->trigger(self::EVENT_BEFORE_SORTING, $event);

        return $event->canSort;
    }

    /**
     * @return PodiumResponse
     */
    public function sort(): PodiumResponse
    {
        if (!$this->beforeSort()) {
            return PodiumResponse::error();
        }
        if (!$this->validate()) {
            Yii::warning(['Forums sort validation failed', $this->errors], 'podium');
            return PodiumResponse::error($this);
        }
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $nextOrder = 0;
            foreach ($this->sortOrder as $forumId) {
                if (static::updateAll([
                        'sort' => $nextOrder,
                        'updated_at' => time(),
                    ], [
                        'id' => $forumId,
                        'category_id' => $this->category_id
                    ]) > 0) {
                    $nextOrder++;
                }
            }
            $transaction->commit();

            $this->afterSort();
            return PodiumResponse::success();

        } catch (\Throwable $exc) {
            Yii::error(['Exception while sorting forums', $exc->getMessage(), $exc->getTraceAsString()], 'podium');
            try {
                $transaction->rollBack();
            } catch (\Throwable $excTrans) {
                Yii::error(['Exception while forums sorting transaction rollback', $excTrans->getMessage(), $excTrans->getTraceAsString()], 'podium');
            }
            return PodiumResponse::error();
        }
    }

    public function afterSort(): void
    {
        $this->trigger(self::EVENT_AFTER_SORTING);
    }

    /**
     * @param ModelInterface $forum
     * @throws NotSupportedException
     */
    public function setForum(ModelInterface $forum): void
    {
        throw new NotSupportedException('Forum can not be sorted in Forum.');
    }

    /**
     * @param ModelInterface $thread
     * @throws NotSupportedException
     */
    public function setThread(ModelInterface $thread): void
    {
        throw new NotSupportedException('Forum can not be sorted in Thread.');
    }
}
