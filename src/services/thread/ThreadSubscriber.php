<?php

declare(strict_types=1);

namespace bizley\podium\api\services\thread;

use bizley\podium\api\components\PodiumResponse;
use bizley\podium\api\events\SubscriptionEvent;
use bizley\podium\api\interfaces\MemberRepositoryInterface;
use bizley\podium\api\interfaces\SubscriberInterface;
use bizley\podium\api\interfaces\SubscriptionRepositoryInterface;
use bizley\podium\api\interfaces\ThreadRepositoryInterface;
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
     * @throws InvalidConfigException
     */
    public function getSubscription(): SubscriptionRepositoryInterface
    {
        if (null === $this->subscription) {
            /** @var SubscriptionRepositoryInterface $subscription */
            $subscription = Instance::ensure($this->repositoryConfig, SubscriptionRepositoryInterface::class);
            $this->subscription = $subscription;
        }

        return $this->subscription;
    }

    /**
     * Calls before subscribing to the thread.
     */
    public function beforeSubscribe(): bool
    {
        $event = new SubscriptionEvent();
        $this->trigger(self::EVENT_BEFORE_SUBSCRIBING, $event);

        return $event->canSubscribe;
    }

    /**
     * Subscribes to the thread.
     */
    public function subscribe(ThreadRepositoryInterface $thread, MemberRepositoryInterface $member): PodiumResponse
    {
        if (!$this->beforeSubscribe()) {
            return PodiumResponse::error();
        }

        try {
            $subscription = $this->getSubscription();
            if ($subscription->isMemberSubscribed($member, $thread)) {
                return PodiumResponse::error(['api' => Yii::t('podium.error', 'thread.already.subscribed')]);
            }

            if (!$subscription->subscribe($member, $thread)) {
                return PodiumResponse::error($subscription->getErrors());
            }

            $this->afterSubscribe($subscription);

            return PodiumResponse::success();
        } catch (Throwable $exc) {
            Yii::error(['Exception while subscribing thread', $exc->getMessage(), $exc->getTraceAsString()], 'podium');

            return PodiumResponse::error(['exception' => $exc]);
        }
    }

    /**
     * Calls after subscribing to the thread.
     */
    public function afterSubscribe(SubscriptionRepositoryInterface $subscription): void
    {
        $this->trigger(self::EVENT_AFTER_SUBSCRIBING, new SubscriptionEvent(['repository' => $subscription]));
    }

    /**
     * Calls before unsubscribing from the thread.
     */
    public function beforeUnsubscribe(): bool
    {
        $event = new SubscriptionEvent();
        $this->trigger(self::EVENT_BEFORE_UNSUBSCRIBING, $event);

        return $event->canUnsubscribe;
    }

    /**
     * Unsubscribes from the thread.
     */
    public function unsubscribe(ThreadRepositoryInterface $thread, MemberRepositoryInterface $member): PodiumResponse
    {
        if (!$this->beforeUnsubscribe()) {
            return PodiumResponse::error();
        }

        try {
            $subscription = $this->getSubscription();
            if (!$subscription->fetchOne($member, $thread)) {
                return PodiumResponse::error(['api' => Yii::t('podium.error', 'thread.not.subscribed')]);
            }

            if (!$subscription->delete()) {
                return PodiumResponse::error($subscription->getErrors());
            }

            $this->afterUnsubscribe();

            return PodiumResponse::success();
        } catch (Throwable $exc) {
            Yii::error(
                ['Exception while unsubscribing thread', $exc->getMessage(), $exc->getTraceAsString()],
                'podium'
            );

            return PodiumResponse::error(['exception' => $exc]);
        }
    }

    /**
     * Calls after unsubscribing from the thread successfully.
     */
    public function afterUnsubscribe(): void
    {
        $this->trigger(self::EVENT_AFTER_UNSUBSCRIBING);
    }
}
