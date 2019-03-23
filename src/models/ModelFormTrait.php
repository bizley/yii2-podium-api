<?php

declare(strict_types=1);

namespace bizley\podium\api\models;

use bizley\podium\api\interfaces\ModelFormInterface;

/**
 * Trait ModelFormTrait
 * @package bizley\podium\api\models
 */
trait ModelFormTrait
{
    /**
     * @param int $modelId
     * @return ModelFormInterface|null
     */
    public static function findById(int $modelId): ?ModelFormInterface
    {
        return static::findOne(['id' => $modelId]);
    }

    /**
     * By default this will be inherited from \yii\db\BaseActiveRecord
     * @param mixed $condition
     * @return ModelFormInterface|static|null
     */
    abstract public static function findOne($condition); // BC declaration

    /**
     * @param array $data
     * @return bool
     */
    public function loadData(array $data = []): bool
    {
        return $this->load($data, '');
    }

    /**
     * By default this is inherited from \yii\base\Model
     * @param array $data
     * @param string|null $formName
     * @return bool
     */
    abstract public function load($data, $formName = null); // BC declaration
}
