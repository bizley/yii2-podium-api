<?php

declare(strict_types=1);

namespace bizley\podium\api\models\category;

use bizley\podium\api\base\PodiumResponse;
use bizley\podium\api\events\SortEvent;
use bizley\podium\api\interfaces\ModelInterface;
use bizley\podium\api\interfaces\SortableInterface;
use bizley\podium\api\repos\CategoryRepo;
use Yii;
use yii\base\NotSupportedException;

/**
 * Class CategorySorter
 * @package bizley\podium\api\models\category
 */
class CategorySorter extends CategoryRepo implements SortableInterface
{
    public const EVENT_BEFORE_SORTING = 'podium.category.sorting.before';
    public const EVENT_AFTER_SORTING = 'podium.category.sorting.after';

    public $sortOrder;

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
            'sortOrder' => Yii::t('podium.label', 'category.sort.order'),
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
            Yii::warning(['Categories sort validation failed', $this->errors], 'podium');
            return PodiumResponse::error($this);
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $nextOrder = 0;
            foreach ($this->sortOrder as $categoryId) {
                if (static::updateAll([
                        'sort' => $nextOrder,
                        'updated_at' => time(),
                    ], ['id' => $categoryId]) > 0) {
                    $nextOrder++;
                }
            }

            $transaction->commit();

            $this->afterSort();

            return PodiumResponse::success();

        } catch (\Throwable $exc) {
            Yii::error(['Exception while sorting categories', $exc->getMessage(), $exc->getTraceAsString()], 'podium');
            try {
                $transaction->rollBack();
            } catch (\Throwable $excTrans) {
                Yii::error(['Exception while categories sorting transaction rollback', $excTrans->getMessage(), $excTrans->getTraceAsString()], 'podium');
            }
            return PodiumResponse::error($this);
        }
    }

    public function afterSort(): void
    {
        $this->trigger(self::EVENT_AFTER_SORTING);
    }

    /**
     * @param ModelInterface $category
     * @throws NotSupportedException
     */
    public function setCategory(ModelInterface $category): void
    {
        throw new NotSupportedException('Category can not be sorted in Category.');
    }

    /**
     * @param ModelInterface $forum
     * @throws NotSupportedException
     */
    public function setForum(ModelInterface $forum): void
    {
        throw new NotSupportedException('Category can not be sorted in Forum.');
    }

    /**
     * @param ModelInterface $thread
     * @throws NotSupportedException
     */
    public function setThread(ModelInterface $thread): void
    {
        throw new NotSupportedException('Category can not be sorted in Thread.');
    }
}
