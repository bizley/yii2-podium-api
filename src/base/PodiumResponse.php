<?php

declare(strict_types=1);

namespace bizley\podium\api\base;

use yii\base\Component;
use yii\base\Model;

/**
 * Class PodiumResponse
 * @package bizley\podium\api\base
 */
class PodiumResponse extends Component
{
    /**
     * @var bool
     */
    public $result;

    /**
     * @var array
     */
    public $errors = [];

    /**
     * Returns successful response.
     * @return PodiumResponse
     */
    public static function success(): PodiumResponse
    {
        return new static(['result' => true]);
    }

    /**
     * Returns erroneous response.
     * @param Model|null $model
     * @return PodiumResponse
     */
    public static function error(?Model $model = null): PodiumResponse
    {
        return new static([
            'result' => false,
            'errors' => $model ? $model->errors : [],
        ]);
    }
}
