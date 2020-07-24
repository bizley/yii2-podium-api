<?php

declare(strict_types=1);

namespace bizley\podium\api\models\thread;

use bizley\podium\api\base\PodiumResponse;
use bizley\podium\api\events\SubscriptionEvent;
use bizley\podium\api\InsufficientDataException;
use bizley\podium\api\interfaces\MembershipInterface;
use bizley\podium\api\interfaces\ModelInterface;
use bizley\podium\api\interfaces\SubscriberInterface;
use bizley\podium\api\repos\SubscriptionRepo;
use Throwable;
use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * Class ThreadSubscriber
 * @package bizley\podium\api\models\thread
 */
class ThreadSubscriber extends SubscriptionRepo implements SubscriberInterface
{
    public const EVENT_BEFORE_SUBSCRIBING = 'podium.subscription.subscribing.before';
    public const EVENT_AFTER_SUBSCRIBING = 'podium.subscription.subscribing.after';
    public const EVENT_BEFORE_UNSUBSCRIBING = 'podium.subscription.unsubscribing.before';
    public const EVENT_AFTER_UNSUBSCRIBING = 'podium.subscription.unsubscribing.after';

    /**
     * @return array<string|int, mixed>
     */
    public function behaviors(): array
    {
        return ['timestamp' => TimestampBehavior::class];
    }

    /**
     * @param MembershipInterface $member
     * @throws InsufficientDataException
     */
    public function setMember(MembershipInterface $member): void
    {
        $memberId = $member->getId();
        if ($memberId === null) {
            throw new InsufficientDataException('No member Id provided for thread subscriber');
        }
        $this->member_id = $memberId;
    }

    /**
     * @param ModelInterface $thread
     * @throws InsufficientDataException
     */
    public function setThread(ModelInterface $thread): void
    {
        $threadId = $thread->getId();
        if ($threadId === null) {
            throw new InsufficientDataException('No thread Id provided for thread subscriber');
        }
        $this->thread_id = $threadId;
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

        if (
            static::find()->where([
                'member_id' => $this->member_id,
                'thread_id' => $this->thread_id,
            ])->exists()
        ) {
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
            /** @var ThreadSubscriber $subscription */
            if ($subscription->delete() === false) {
                Yii::error('Error while unsubscribing thread', 'podium');

                return PodiumResponse::error();
            }

            $this->afterUnsubscribe();

            return PodiumResponse::success();
        } catch (Throwable $exc) {
            Yii::error(
                ['Exception while unsubscribing thread', $exc->getMessage(), $exc->getTraceAsString()],
                'podium'
            );

            return PodiumResponse::error();
        }
    }

    public function afterUnsubscribe(): void
    {
        $this->trigger(self::EVENT_AFTER_UNSUBSCRIBING);
    }
}
