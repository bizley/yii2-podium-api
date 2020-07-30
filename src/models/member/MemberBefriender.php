<?php

declare(strict_types=1);

namespace bizley\podium\api\models\member;

use bizley\podium\api\base\PodiumResponse;
use bizley\podium\api\enums\AcquaintanceType;
use bizley\podium\api\events\AcquaintanceEvent;
use bizley\podium\api\InsufficientDataException;
use bizley\podium\api\interfaces\BefrienderInterface;
use bizley\podium\api\interfaces\MembershipInterface;
use bizley\podium\api\repos\AcquaintanceRepo;
use Throwable;
use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * Class MemberBefriender
 * @package bizley\podium\api\models\member
 */
class MemberBefriender extends AcquaintanceRepo implements BefrienderInterface
{
    public const EVENT_BEFORE_BEFRIENDING = 'podium.acquaintance.befriending.before';
    public const EVENT_AFTER_BEFRIENDING = 'podium.acquaintance.befriending.after';
    public const EVENT_BEFORE_UNFRIENDING = 'podium.acquaintance.unfriending.before';
    public const EVENT_AFTER_UNFRIENDING = 'podium.acquaintance.unfriending.after';

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
     * @throws InsufficientDataException
     */
    public function setMember(MembershipInterface $member): void
    {
        $memberId = $member->getId();
        if ($memberId === null) {
            throw new InsufficientDataException('Missing member Id for member befriender');
        }
        $this->member_id = $memberId;
    }

    /**
     * @param MembershipInterface $target
     * @throws InsufficientDataException
     */
    public function setTarget(MembershipInterface $target): void
    {
        $targetId = $target->getId();
        if ($targetId === null) {
            throw new InsufficientDataException('Missing target Id for member befriender');
        }
        $this->target_id = $targetId;
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
     * @return PodiumResponse
     */
    public function befriend(): PodiumResponse
    {
        if (!$this->beforeBefriend()) {
            return PodiumResponse::error();
        }

        if (
            static::find()->where([
                'member_id' => $this->member_id,
                'target_id' => $this->target_id,
            ])->exists()
        ) {
            $this->addError('target_id', Yii::t('podium.error', 'target.already.acquainted'));

            return PodiumResponse::error($this);
        }

        $this->type_id = AcquaintanceType::FRIEND;

        if (!$this->save()) {
            Yii::error(['Error while befriending member', $this->errors], 'podium');

            return PodiumResponse::error($this);
        }

        $this->afterBefriend();

        return PodiumResponse::success();
    }

    public function afterBefriend(): void
    {
        $this->trigger(self::EVENT_AFTER_BEFRIENDING, new AcquaintanceEvent(['model' => $this]));
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
     * @return PodiumResponse
     */
    public function unfriend(): PodiumResponse
    {
        if (!$this->beforeUnfriend()) {
            return PodiumResponse::error();
        }

        /** @var self|null $friendship */
        $friendship = static::find()->where([
            'member_id' => $this->member_id,
            'target_id' => $this->target_id,
            'type_id' => AcquaintanceType::FRIEND,
        ])->one();
        if ($friendship === null) {
            $this->addError('target_id', Yii::t('podium.error', 'target.not.befriended'));

            return PodiumResponse::error($this);
        }

        try {
            if ($friendship->delete() === false) {
                Yii::error('Error while unfriending member', 'podium');
                return PodiumResponse::error();
            }

            $this->afterUnfriend();

            return PodiumResponse::success();
        } catch (Throwable $exc) {
            Yii::error(['Exception while unfriending member', $exc->getMessage(), $exc->getTraceAsString()], 'podium');

            return PodiumResponse::error();
        }
    }

    public function afterUnfriend(): void
    {
        $this->trigger(self::EVENT_AFTER_UNFRIENDING);
    }
}
