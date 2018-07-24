<?php

declare(strict_types=1);

namespace bizley\podium\api\repos;

use yii\db\ActiveRecord;

/**
 * Config Active Record.
 *
 * @property string $param
 * @property string $value
 *
 * TODO: move to Podium client
 */
class ConfigRepo extends ActiveRecord
{
    /**
     * @return string
     */
    public static function tableName(): string
    {
        return '{{%podium_config}}';
    }
}
