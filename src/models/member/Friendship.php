<?php

declare(strict_types=1);

namespace bizley\podium\api\models\member;

use bizley\podium\api\enums\AcquaintanceType;
use bizley\podium\api\events\AcquaintanceEvent;
use bizley\podium\api\interfaces\FriendshipInterface;
use bizley\podium\api\interfaces\MembershipInterface;
use bizley\podium\api\repos\AcquaintanceRepo;
use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * Class Friendship
 * @package bizley\podium\api\models\member
 *
 * @property MembershipInterface $member
 * @property MembershipInterface $target
 */
class Friendship extends AcquaintanceRepo implements FriendshipInterface
{
    public const EVENT_BEFORE_BEFRIENDING = 'podium.acquaintance.befriending.before';
    public const EVENT_AFTER_BEFRIENDING = 'podium.acquaintance.befriending.after';
    public const EVENT_BEFORE_UNFRIENDING = 'podium.acquaintance.unfriending.before';
    public const EVENT_AFTER_UNFRIENDING = 'podium.acquaintance.unfriending.after';

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
     * @param MembershipInterface $member
     */
    public function setMember(MembershipInterface $member): void
    {
        $this->member_id = $member->getId();
    }

    /**
     * @param MembershipInterface $target
     */
    public function setTarget(MembershipInterface $target): void
    {
        $this->target_id = $target->getId();
    }

    /**
     * @return bool
     */
    public function beforeBefriend(): bool
    {
        $event = new AcquaintanceEvent();
        $this->trigger(self::EVENT_BEFORE_BEFRIENDING, $event);

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
            ])->exists()) {
            $this->addError('target_id', Yii::t('podium.error', 'target.already.acquainted'));
            return false;
        }
        if (!$this->save()) {
            Yii::error(['Error while befriending member', $this->errors], 'podium');
            return false;
        }
        $this->afterBefriend();
        return true;
    }

    public function afterBefriend(): void
    {
        $this->trigger(self::EVENT_AFTER_BEFRIENDING, new AcquaintanceEvent([
            'model' => $this
        ]));
    }

    /**
     * @return bool
     */
    public function beforeUnfriend(): bool
    {
        $event = new AcquaintanceEvent();
        $this->trigger(self::EVENT_BEFORE_UNFRIENDING, $event);

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
                Yii::error('Error while unfriending member', 'podium');
                return false;
            }
        } catch (\Throwable $exc) {
            Yii::error(['Exception while unfriending member', $exc->getMessage(), $exc->getTraceAsString()], 'podium');
            return false;
        }

        $this->afterUnfriend();
        return true;
    }

    public function afterUnfriend(): void
    {
        $this->trigger(self::EVENT_AFTER_UNFRIENDING);
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
