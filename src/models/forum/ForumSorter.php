<?php

declare(strict_types=1);

namespace bizley\podium\api\models\forum;

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
     * @return bool
     */
    public function sort(): bool
    {
        if (!$this->beforeSort()) {
            return false;
        }
        if (!$this->validate()) {
            Yii::error(['forum.sort.validate', $this->errors], 'podium');
            return false;
        }
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $nextOrder = 0;
            foreach ($this->sortOrder as $forumId) {
                $result = static::updateAll([
                    'sort' => $nextOrder,
                    'updated_at' => time(),
                ], ['id' => $forumId, 'category_id' => $this->category_id]);
                if ($result > 0) {
                    $nextOrder++;
                }
            }
            $transaction->commit();
            $this->afterSort();
            return true;
        } catch (\Throwable $exc) {
            Yii::error(['forum.sort', $exc->getMessage(), $exc->getTraceAsString()], 'podium');
            try {
                $transaction->rollBack();
            } catch (\Throwable $excTrans) {
                Yii::error(['forum.sort.rollback', $excTrans->getMessage(), $excTrans->getTraceAsString()], 'podium');
            }
            return false;
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
