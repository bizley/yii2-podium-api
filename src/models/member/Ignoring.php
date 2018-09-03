<?php

declare(strict_types=1);

namespace bizley\podium\api\models\member;

use bizley\podium\api\base\PodiumResponse;
use bizley\podium\api\enums\AcquaintanceType;
use bizley\podium\api\events\AcquaintanceEvent;
use bizley\podium\api\interfaces\IgnoringInterface;
use bizley\podium\api\interfaces\MembershipInterface;
use bizley\podium\api\repos\AcquaintanceRepo;
use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * Class Ignore
 * @package bizley\podium\api\models\member
 */
class Ignoring extends AcquaintanceRepo implements IgnoringInterface
{
    public const EVENT_BEFORE_IGNORING = 'podium.acquaintance.ignoring.before';
    public const EVENT_AFTER_IGNORING = 'podium.acquaintance.ignoring.after';
    public const EVENT_BEFORE_UNIGNORING = 'podium.acquaintance.unignoring.before';
    public const EVENT_AFTER_UNIGNORING = 'podium.acquaintance.unignoring.after';

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
    public function beforeIgnore(): bool
    {
        $event = new AcquaintanceEvent();
        $this->trigger(self::EVENT_BEFORE_IGNORING, $event);

        return $event->canIgnore;
    }

    /**
     * @return PodiumResponse
     */
    public function ignore(): PodiumResponse
    {
        if (!$this->beforeIgnore()) {
            return PodiumResponse::error();
        }
        if (static::find()->where([
                'member_id' => $this->member_id,
                'target_id' => $this->target_id,
            ])->exists()) {
            $this->addError('target_id', Yii::t('podium.error', 'target.already.acquainted'));
            return PodiumResponse::error($this);
        }

        $this->type_id = AcquaintanceType::IGNORE;

        if (!$this->save()) {
            Yii::error(['Error while ignoring member', $this->errors], 'podium');
            return PodiumResponse::error($this);
        }

        $this->afterIgnore();
        return PodiumResponse::success();
    }

    public function afterIgnore(): void
    {
        $this->trigger(self::EVENT_AFTER_IGNORING, new AcquaintanceEvent([
            'model' => $this
        ]));
    }

    /**
     * @return bool
     */
    public function beforeUnignore(): bool
    {
        $event = new AcquaintanceEvent();
        $this->trigger(self::EVENT_BEFORE_UNIGNORING, $event);

        return $event->canUnignore;
    }

    /**
     * @return PodiumResponse
     */
    public function unignore(): PodiumResponse
    {
        if (!$this->beforeUnignore()) {
            return PodiumResponse::error();
        }

        $ignoring = static::find()->where([
            'member_id' => $this->member_id,
            'target_id' => $this->target_id,
            'type_id' => AcquaintanceType::IGNORE,
        ])->one();
        if ($ignoring === null) {
            $this->addError('target_id', Yii::t('podium.error', 'target.not.ignored'));
            return PodiumResponse::error($this);
        }

        try {
            if (!$ignoring->delete()) {
                Yii::error('Error while unignoring member', 'podium');
                return PodiumResponse::error();
            }
        } catch (\Throwable $exc) {
            Yii::error(['Exception while unignoring member', $exc->getMessage(), $exc->getTraceAsString()], 'podium');
            return PodiumResponse::error();
        }

        $this->afterUnignore();
        return PodiumResponse::success();
    }

    public function afterUnignore(): void
    {
        $this->trigger(self::EVENT_AFTER_UNIGNORING);
    }

    /**
     * @return bool
     */
    public function isIgnoring(): bool
    {
        return static::find()->where([
            'member_id' => $this->member_id,
            'target_id' => $this->target_id,
            'type_id' => $this->type_id,
        ])->exists();
    }
}
