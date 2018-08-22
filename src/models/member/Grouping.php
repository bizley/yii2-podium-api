<?php

declare(strict_types=1);

namespace bizley\podium\api\models\member;

use bizley\podium\api\base\PodiumResponse;
use bizley\podium\api\events\GroupEvent;
use bizley\podium\api\interfaces\GroupingInterface;
use bizley\podium\api\interfaces\MembershipInterface;
use bizley\podium\api\interfaces\ModelInterface;
use bizley\podium\api\repos\GroupMemberRepo;
use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * Class Grouping
 * @package bizley\podium\api\models\member
 */
class Grouping extends GroupMemberRepo implements GroupingInterface
{
    public const EVENT_BEFORE_JOINING = 'podium.group.joining.before';
    public const EVENT_AFTER_JOINING = 'podium.group.joining.after';
    public const EVENT_BEFORE_LEAVING = 'podium.group.leaving.before';
    public const EVENT_AFTER_LEAVING = 'podium.group.leaving.after';

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
     * @param ModelInterface $group
     */
    public function setGroup(ModelInterface $group): void
    {
        $this->group_id = $group->getId();
    }

    /**
     * @return bool
     */
    public function beforeJoin(): bool
    {
        $event = new GroupEvent();
        $this->trigger(self::EVENT_BEFORE_JOINING, $event);

        return $event->canJoin;
    }

    /**
     * @return PodiumResponse
     */
    public function join(): PodiumResponse
    {
        if (!$this->beforeJoin()) {
            return PodiumResponse::error();
        }

        if (static::find()->where([
                'member_id' => $this->member_id,
                'group_id' => $this->group_id,
            ])->exists()) {
            $this->addError('group_id', Yii::t('podium.error', 'group.already.joined'));
            return PodiumResponse::error($this);
        }

        if (!$this->save()) {
            Yii::error(['Error while joining group', $this->errors], 'podium');
            return PodiumResponse::error($this);
        }

        $this->afterJoin();
        return PodiumResponse::success();
    }

    public function afterJoin(): void
    {
        $this->trigger(self::EVENT_AFTER_JOINING, new GroupEvent([
            'model' => $this
        ]));
    }

    /**
     * @return bool
     */
    public function beforeLeave(): bool
    {
        $event = new GroupEvent();
        $this->trigger(self::EVENT_BEFORE_LEAVING, $event);

        return $event->canLeave;
    }

    /**
     * @return PodiumResponse
     */
    public function leave(): PodiumResponse
    {
        if (!$this->beforeLeave()) {
            return PodiumResponse::error();
        }

        $groupMember = static::find()->where([
            'member_id' => $this->member_id,
            'group_id' => $this->group_id,
        ])->one();
        if ($groupMember === null) {
            $this->addError('group_id', Yii::t('podium.error', 'group.not.joined'));
            return PodiumResponse::error($this);
        }

        try {
            if (!$groupMember->delete()) {
                Yii::error(['Error while leaving group', $this->errors], 'podium');
                return PodiumResponse::error($this);
            }
        } catch (\Throwable $exc) {
            Yii::error(['Exception while leaving group', $exc->getMessage(), $exc->getTraceAsString()], 'podium');
            return PodiumResponse::error($this);
        }

        $this->afterLeave();
        return PodiumResponse::success();
    }

    public function afterLeave(): void
    {
        $this->trigger(self::EVENT_AFTER_LEAVING);
    }
}
