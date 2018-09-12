<?php

declare(strict_types=1);

namespace bizley\podium\api\models\thread;

use bizley\podium\api\base\PodiumResponse;
use bizley\podium\api\events\SubscriptionEvent;
use bizley\podium\api\interfaces\MembershipInterface;
use bizley\podium\api\interfaces\ModelInterface;
use bizley\podium\api\interfaces\SubscribingInterface;
use bizley\podium\api\repos\SubscriptionRepo;
use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * Class Subscribing
 * @package bizley\podium\api\models\thread
 */
class Subscribing extends SubscriptionRepo implements SubscribingInterface
{
    public const EVENT_BEFORE_SUBSCRIBING = 'podium.subscription.subscribing.before';
    public const EVENT_AFTER_SUBSCRIBING = 'podium.subscription.subscribing.after';
    public const EVENT_BEFORE_UNSUBSCRIBING = 'podium.subscription.unsubscribing.before';
    public const EVENT_AFTER_UNSUBSCRIBING = 'podium.subscription.unsubscribing.after';

    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return ['timestamp' => TimestampBehavior::class];
    }

    /**
     * @param MembershipInterface $member
     */
    public function setMember(MembershipInterface $member): void
    {
        $this->member_id = $member->getId();
    }

    /**
     * @param ModelInterface $thread
     */
    public function setThread(ModelInterface $thread): void
    {
        $this->thread_id = $thread->getId();
    }

    /**
     * @return bool
     */
    public function beforeSubscribe(): bool
    {
        $event = new SubscriptionEvent();
        $this->trigger(self::EVENT_BEFORE_SUBSCRIBING, $event);

        return $event->canSubscribe;
    }

    /**
     * @return PodiumResponse
     */
    public function subscribe(): PodiumResponse
    {
        if (!$this->beforeSubscribe()) {
            return PodiumResponse::error();
        }

        if (static::find()->where([
                'member_id' => $this->member_id,
                'thread_id' => $this->thread_id,
            ])->exists()) {
            $this->addError('thread_id', Yii::t('podium.error', 'thread.already.subscribed'));
            return PodiumResponse::error($this);
        }

        if (!$this->save()) {
            Yii::error(['Error while subscribing thread', $this->errors], 'podium');
            return PodiumResponse::error($this);
        }

        $this->afterSubscribe();

        return PodiumResponse::success();
    }

    public function afterSubscribe(): void
    {
        $this->trigger(self::EVENT_AFTER_SUBSCRIBING, new SubscriptionEvent(['model' => $this]));
    }

    /**
     * @return bool
     */
    public function beforeUnsubscribe(): bool
    {
        $event = new SubscriptionEvent();
        $this->trigger(self::EVENT_BEFORE_UNSUBSCRIBING, $event);

        return $event->canUnsubscribe;
    }

    /**
     * @return PodiumResponse
     */
    public function unsubscribe(): PodiumResponse
    {
        if (!$this->beforeUnsubscribe()) {
            return PodiumResponse::error();
        }

        $subscription = static::find()->where([
            'member_id' => $this->member_id,
            'thread_id' => $this->thread_id,
        ])->one();
        if ($subscription === null) {
            $this->addError('thread_id', Yii::t('podium.error', 'thread.not.subscribed'));
            return PodiumResponse::error($this);
        }

        try {
            if ($subscription->delete() === false) {
                Yii::error('Error while unsubscribing thread', 'podium');
                return PodiumResponse::error();
            }

            $this->afterUnsubscribe();

            return PodiumResponse::success();

        } catch (\Throwable $exc) {
            Yii::error(['Exception while unsubscribing thread', $exc->getMessage(), $exc->getTraceAsString()], 'podium');
            return PodiumResponse::error();
        }
    }

    public function afterUnsubscribe(): void
    {
        $this->trigger(self::EVENT_AFTER_UNSUBSCRIBING);
    }
}
