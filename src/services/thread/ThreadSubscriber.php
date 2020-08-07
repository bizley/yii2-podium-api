<?php

declare(strict_types=1);

namespace bizley\podium\api\services\thread;

use bizley\podium\api\base\PodiumResponse;
use bizley\podium\api\events\SubscriptionEvent;
use bizley\podium\api\InsufficientDataException;
use bizley\podium\api\interfaces\MembershipInterface;
use bizley\podium\api\interfaces\ModelInterface;
use bizley\podium\api\interfaces\SubscriberInterface;
use bizley\podium\api\interfaces\SubscriptionRepositoryInterface;
use bizley\podium\api\repositories\SubscriptionRepository;
use Throwable;
use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\di\Instance;

final class ThreadSubscriber extends Component implements SubscriberInterface
{
    public const EVENT_BEFORE_SUBSCRIBING = 'podium.subscription.subscribing.before';
    public const EVENT_AFTER_SUBSCRIBING = 'podium.subscription.subscribing.after';
    public const EVENT_BEFORE_UNSUBSCRIBING = 'podium.subscription.unsubscribing.before';
    public const EVENT_AFTER_UNSUBSCRIBING = 'podium.subscription.unsubscribing.after';

    private ?SubscriptionRepositoryInterface $subscription = null;

    /**
     * @var string|array|SubscriptionRepositoryInterface
     */
    public $repositoryConfig = SubscriptionRepository::class;

    /**
     * @return SubscriptionRepositoryInterface
     * @throws InvalidConfigException
     */
    public function getSubscription(): SubscriptionRepositoryInterface
    {
        if ($this->subscription === null) {
            /** @var SubscriptionRepositoryInterface $subscription */
            $subscription = Instance::ensure($this->repositoryConfig, SubscriptionRepositoryInterface::class);
            $this->subscription = $subscription;
        }
        return $this->subscription;
    }

    public function beforeSubscribe(): bool
    {
        $event = new SubscriptionEvent();
        $this->trigger(self::EVENT_BEFORE_SUBSCRIBING, $event);

        return $event->canSubscribe;
    }

    /**
     * @param MembershipInterface $member
     * @param ModelInterface $thread
     * @return PodiumResponse
     * @throws InsufficientDataException
     * @throws InvalidConfigException
     */
    public function subscribe(MembershipInterface $member, ModelInterface $thread): PodiumResponse
    {
        if (!$this->beforeSubscribe()) {
            return PodiumResponse::error();
        }

        $memberId = $member->getId();
        if ($memberId === null) {
            throw new InsufficientDataException('Missing member ID for Thread Subscriber!');
        }
        $threadId = $thread->getId();
        if ($threadId === null) {
            throw new InsufficientDataException('Missing thread ID for Thread Subscriber!');
        }

        $subscription = $this->getSubscription();
        if ($subscription->isMemberSubscribed($memberId, $threadId)) {
            return PodiumResponse::error(['api' => Yii::t('podium.error', 'thread.already.subscribed')]);
        }

        try {
            if (!$subscription->subscribe($memberId, $threadId)) {
                return PodiumResponse::error($subscription->getErrors());
            }

            $this->afterSubscribe();

            return PodiumResponse::success();
        } catch (Throwable $exc) {
            Yii::error(['Exception while subscribing thread', $exc->getMessage(), $exc->getTraceAsString()], 'podium');
            return PodiumResponse::error();
        }
    }

    public function afterSubscribe(): void
    {
        $this->trigger(self::EVENT_AFTER_SUBSCRIBING, new SubscriptionEvent(['model' => $this]));
    }

    public function beforeUnsubscribe(): bool
    {
        $event = new SubscriptionEvent();
        $this->trigger(self::EVENT_BEFORE_UNSUBSCRIBING, $event);

        return $event->canUnsubscribe;
    }

    /**
     * @param MembershipInterface $member
     * @param ModelInterface $thread
     * @return PodiumResponse
     * @throws InsufficientDataException
     * @throws InvalidConfigException
     */
    public function unsubscribe(MembershipInterface $member, ModelInterface $thread): PodiumResponse
    {
        if (!$this->beforeUnsubscribe()) {
            return PodiumResponse::error();
        }

        $memberId = $member->getId();
        if ($memberId === null) {
            throw new InsufficientDataException('Missing member ID for Thread Subscriber!');
        }
        $threadId = $thread->getId();
        if ($threadId === null) {
            throw new InsufficientDataException('Missing thread ID for Thread Subscriber!');
        }
        $subscription = $this->getSubscription();
        if (!$subscription->find($memberId, $threadId)) {
            return PodiumResponse::error(['api' => Yii::t('podium.error', 'thread.not.subscribed')]);
        }
        try {
            if (!$subscription->delete()) {
                return PodiumResponse::error($subscription->getErrors());
            }

            $this->afterUnsubscribe();

            return PodiumResponse::success();
        } catch (Throwable $exception) {
            Yii::error(
                ['Exception while unsubscribing thread', $exception->getMessage(), $exception->getTraceAsString()],
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
