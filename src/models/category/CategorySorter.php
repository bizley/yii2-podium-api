<?php

declare(strict_types=1);

namespace bizley\podium\api\models\category;

use bizley\podium\api\events\SortEvent;
use bizley\podium\api\interfaces\SortableInterface;
use bizley\podium\api\repos\CategoryRepo;
use Yii;

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
     * @return bool
     */
    public function sort(): bool
    {
        if (!$this->beforeSort()) {
            return false;
        }
        if (!$this->validate()) {
            Yii::error(['category.sort.validate', $this->errors], 'podium');
            return false;
        }
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $nextOrder = 0;
            foreach ($this->sortOrder as $categoryId) {
                $result = static::updateAll([
                    'sort' => $nextOrder,
                    'updated_at' => time(),
                ], ['id' => $categoryId]);
                if ($result > 0) {
                    $nextOrder++;
                }
            }
            $transaction->commit();
            $this->afterSort();
            return true;
        } catch (\Throwable $exc) {
            Yii::error(['category.sort', $exc->getMessage(), $exc->getTraceAsString()], 'podium');
            try {
                $transaction->rollBack();
            } catch (\Throwable $excTrans) {
                Yii::error(['category.sort.rollback', $excTrans->getMessage(), $excTrans->getTraceAsString()], 'podium');
            }
            return false;
        }
    }

    public function afterSort(): void
    {
        $this->trigger(self::EVENT_AFTER_SORTING);
    }
}
