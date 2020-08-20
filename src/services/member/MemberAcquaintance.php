<?php

declare(strict_types=1);

namespace bizley\podium\api\services\member;

use bizley\podium\api\components\PodiumResponse;
use bizley\podium\api\events\AcquaintanceEvent;
use bizley\podium\api\interfaces\AcquaintanceInterface;
use bizley\podium\api\interfaces\AcquaintanceRepositoryInterface;
use bizley\podium\api\interfaces\MemberRepositoryInterface;
use bizley\podium\api\repositories\AcquaintanceRepository;
use Throwable;
use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\di\Instance;

final class MemberAcquaintance extends Component implements AcquaintanceInterface
{
    public const EVENT_BEFORE_BEFRIENDING = 'podium.acquaintance.befriending.before';
    public const EVENT_AFTER_BEFRIENDING = 'podium.acquaintance.befriending.after';
    public const EVENT_BEFORE_UNFRIENDING = 'podium.acquaintance.unfriending.before';
    public const EVENT_AFTER_UNFRIENDING = 'podium.acquaintance.unfriending.after';
    public const EVENT_BEFORE_IGNORING = 'podium.acquaintance.ignoring.before';
    public const EVENT_AFTER_IGNORING = 'podium.acquaintance.ignoring.after';
    public const EVENT_BEFORE_UNIGNORING = 'podium.acquaintance.unignoring.before';
    public const EVENT_AFTER_UNIGNORING = 'podium.acquaintance.unignoring.after';

    private ?AcquaintanceRepositoryInterface $acquaintance = null;

    /**
     * @var string|array|AcquaintanceRepositoryInterface
     */
    public $repositoryConfig = AcquaintanceRepository::class;

    /**
     * @throws InvalidConfigException
     */
    private function getAcquaintance(): AcquaintanceRepositoryInterface
    {
        if (null === $this->acquaintance) {
            /** @var AcquaintanceRepositoryInterface $acquaintance */
            $acquaintance = Instance::ensure($this->repositoryConfig, AcquaintanceRepositoryInterface::class);
            $this->acquaintance = $acquaintance;
        }

        return $this->acquaintance;
    }

    public function beforeBefriend(): bool
    {
        $event = new AcquaintanceEvent();
        $this->trigger(self::EVENT_BEFORE_BEFRIENDING, $event);

        return $event->canBeFriends;
    }

    public function befriend($id, MemberRepositoryInterface $member): PodiumResponse
    {
        if (!$this->beforeBefriend()) {
            return PodiumResponse::error();
        }

        try {
            $acquaintance = $this->getAcquaintance();

            $memberId = $member->getId();
            if (!$acquaintance->fetchOne($id, $memberId)) {
                $acquaintance->prepare($id, $memberId);
            }
            if (!$acquaintance->befriend()) {
                return PodiumResponse::error($acquaintance->getErrors());
            }

            $this->afterBefriend();

            return PodiumResponse::success();
        } catch (Throwable $exc) {
            Yii::error(['Exception while befriending member', $exc->getMessage(), $exc->getTraceAsString()], 'podium');

            return PodiumResponse::error();
        }
    }

    public function afterBefriend(): void
    {
        $this->trigger(self::EVENT_AFTER_BEFRIENDING);
    }

    public function beforeUnfriend(): bool
    {
        $event = new AcquaintanceEvent();
        $this->trigger(self::EVENT_BEFORE_UNFRIENDING, $event);

        return $event->canUnfriend;
    }

    public function unfriend($id, MemberRepositoryInterface $member): PodiumResponse
    {
        if (!$this->beforeUnfriend()) {
            return PodiumResponse::error();
        }

        try {
            $acquaintance = $this->getAcquaintance();

            $memberId = $member->getId();
            if (!$acquaintance->fetchOne($id, $memberId)) {
                return PodiumResponse::error(['api' => Yii::t('podium.error', 'acquaintance.not.exists')]);
            }
            if ($acquaintance->isIgnoring()) {
                return PodiumResponse::error(['api' => Yii::t('podium.error', 'member.ignores.target')]);
            }

            if (!$acquaintance->delete()) {
                return PodiumResponse::error();
            }

            $this->afterBefriend();

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

    public function beforeIgnore(): bool
    {
        $event = new AcquaintanceEvent();
        $this->trigger(self::EVENT_BEFORE_IGNORING, $event);

        return $event->canIgnore;
    }

    public function ignore($id, MemberRepositoryInterface $member): PodiumResponse
    {
        if (!$this->beforeIgnore()) {
            return PodiumResponse::error();
        }

        try {
            $acquaintance = $this->getAcquaintance();

            $memberId = $member->getId();
            if (!$acquaintance->fetchOne($id, $memberId)) {
                $acquaintance->prepare($id, $memberId);
            }
            if (!$acquaintance->ignore()) {
                return PodiumResponse::error($acquaintance->getErrors());
            }

            $this->afterIgnore();

            return PodiumResponse::success();
        } catch (Throwable $exc) {
            Yii::error(['Exception while ignoring member', $exc->getMessage(), $exc->getTraceAsString()], 'podium');

            return PodiumResponse::error();
        }
    }

    public function afterIgnore(): void
    {
        $this->trigger(self::EVENT_AFTER_IGNORING);
    }

    public function beforeUnignore(): bool
    {
        $event = new AcquaintanceEvent();
        $this->trigger(self::EVENT_BEFORE_UNFRIENDING, $event);

        return $event->canUnfriend;
    }

    public function unignore($id, MemberRepositoryInterface $member): PodiumResponse
    {
        if (!$this->beforeUnignore()) {
            return PodiumResponse::error();
        }

        try {
            $acquaintance = $this->getAcquaintance();

            $memberId = $member->getId();
            if (!$acquaintance->fetchOne($id, $memberId)) {
                return PodiumResponse::error(['api' => Yii::t('podium.error', 'acquaintance.not.exists')]);
            }
            if ($acquaintance->isFriend()) {
                return PodiumResponse::error(['api' => Yii::t('podium.error', 'member.befriends.target')]);
            }

            if (!$acquaintance->delete()) {
                return PodiumResponse::error();
            }

            $this->afterUnignore();

            return PodiumResponse::success();
        } catch (Throwable $exc) {
            Yii::error(['Exception while unignoring member', $exc->getMessage(), $exc->getTraceAsString()], 'podium');

            return PodiumResponse::error();
        }
    }

    public function afterUnignore(): void
    {
        $this->trigger(self::EVENT_AFTER_UNFRIENDING);
    }
}
