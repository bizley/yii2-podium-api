<?php

declare(strict_types=1);

namespace bizley\podium\api\models;

use bizley\podium\api\enums\AcquaintanceType;
use bizley\podium\api\interfaces\FriendshipInterface;
use bizley\podium\api\repos\AcquaintanceRepo;
use bizley\podium\events\AcquaintanceEvent;
use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * Class Friendship
 * @package bizley\podium\api\models
 */
class Friendship extends AcquaintanceRepo implements FriendshipInterface
{
    /**
     * Sets acquaintance type.
     */
    public function init(): void
    {
        parent::init();
        $this->type_id = AcquaintanceType::FRIEND;
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return [
            'timestamp' => [
                'class' => TimestampBehavior::class,
                'updatedAtAttribute' => false,
            ],
        ];
    }

    /**
     * @param int $memberId
     */
    public function setMember(int $memberId): void
    {
        $this->member_id = $memberId;
    }

    /**
     * @param int $targetId
     */
    public function setTarget(int $targetId): void
    {
        $this->target_id = $targetId;
    }

    /**
     * @return bool
     */
    public function beforeBefriend(): bool
    {
        $event = new AcquaintanceEvent();
        $this->trigger(FriendshipInterface::EVENT_BEFORE_BEFRIENDING, $event);

        return $event->canBeFriends;
    }

    /**
     * @return bool
     */
    public function befriend(): bool
    {
        if (!$this->beforeBefriend()) {
            return false;
        }
        if (static::find()->where([
                'member_id' => $this->member_id,
                'target_id' => $this->target_id,
                'type_id' => $this->type_id,
            ])->exists()) {
            $this->addError('target_id', Yii::t('podium.error', 'target.already.befriended'));
            return false;
        }
        if (!$this->save()) {
            Yii::error(['befriend.save', $this->errors], 'podium');
            return false;
        }
        $this->afterBefriend();
        return true;
    }

    public function afterBefriend(): void
    {
        $this->trigger(FriendshipInterface::EVENT_AFTER_BEFRIENDING, new AcquaintanceEvent([
            'acquaintance' => $this
        ]));
    }

    /**
     * @return bool
     */
    public function beforeUnfriend(): bool
    {
        $event = new AcquaintanceEvent();
        $this->trigger(FriendshipInterface::EVENT_BEFORE_UNFRIENDING, $event);

        return $event->canUnfriend;
    }

    /**
     * @return bool
     */
    public function unfriend(): bool
    {
        if (!$this->beforeUnfriend()) {
            return false;
        }
        $friendship = static::find()->where([
            'member_id' => $this->member_id,
            'target_id' => $this->target_id,
            'type_id' => $this->type_id,
        ])->one();
        if ($friendship === null) {
            $this->addError('target_id', Yii::t('podium.error', 'target.not.befriended'));
            return false;
        }
        try {
            if (!$friendship->delete()) {
                Yii::error('unfriend.delete', 'podium');
                return false;
            }
        } catch (\Throwable $exc) {
            Yii::error(['unfriend.delete', $exc->getMessage(), $exc->getTraceAsString()], 'podium');
            return false;
        }

        $this->afterUnfriend();
        return true;
    }

    public function afterUnfriend(): void
    {
        $this->trigger(FriendshipInterface::EVENT_AFTER_UNFRIENDING);
    }

    /**
     * @return bool
     */
    public function isFriend(): bool
    {
        return static::find()->where([
                'member_id' => $this->member_id,
                'target_id' => $this->target_id,
                'type_id' => $this->type_id,
            ])->exists();
    }
}
