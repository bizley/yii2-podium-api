<?php

declare(strict_types=1);

namespace bizley\podium\api\enums;

use Yii;

final class PollAnswerAction extends BaseEnum
{
    public const ADD = 'add';
    public const EDIT = 'edit';
    public const REMOVE = 'remove';

    /**
     * {@inheritdoc}
     */
    public static function data(): array
    {
        return [
            self::ADD => Yii::t('podium.enum', 'poll.answer.action.add'),
            self::EDIT => Yii::t('podium.enum', 'poll.answer.action.edit'),
            self::REMOVE => Yii::t('podium.enum', 'poll.answer.action.remove'),
        ];
    }
}
