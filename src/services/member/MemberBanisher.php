<?php

declare(strict_types=1);

namespace bizley\podium\api\services\member;

use bizley\podium\api\components\PodiumResponse;
use bizley\podium\api\events\BanEvent;
use bizley\podium\api\interfaces\BanisherInterface;
use bizley\podium\api\interfaces\MemberRepositoryInterface;
use bizley\podium\api\repositories\MemberRepository;
use Throwable;
use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\di\Instance;

final class MemberBanisher extends Component implements BanisherInterface
{
    public const EVENT_BEFORE_BANNING = 'podium.member.banning.before';
    public const EVENT_AFTER_BANNING = 'podium.member.banning.after';
    public const EVENT_BEFORE_UNBANNING = 'podium.member.unbanning.before';
    public const EVENT_AFTER_UNBANNING = 'podium.member.unbanning.after';

    private ?MemberRepositoryInterface $member = null;

    /**
     * @var string|array|MemberRepositoryInterface
     */
    public $repositoryConfig = MemberRepository::class;

    /**
     * @throws InvalidConfigException
     */
    private function getMember(): MemberRepositoryInterface
    {
        if (null === $this->member) {
            /** @var MemberRepositoryInterface $member */
            $member = Instance::ensure($this->repositoryConfig, MemberRepositoryInterface::class);
            $this->member = $member;
        }

        return $this->member;
    }

    public function beforeBan(): bool
    {
        $event = new BanEvent();
        $this->trigger(self::EVENT_BEFORE_BANNING, $event);

        return $event->canBan;
    }

    public function ban($id): PodiumResponse
    {
        if (!$this->beforeBan()) {
            return PodiumResponse::error();
        }

        try {
            $member = $this->getMember();
            if (!$member->fetchOne($id)) {
                return PodiumResponse::error(['api' => Yii::t('podium.error', 'member.not.exists')]);
            }
            if (!$member->ban()) {
                return PodiumResponse::error($member->getErrors());
            }

            $this->afterBan();

            return PodiumResponse::success();
        } catch (Throwable $exc) {
            Yii::error(['Exception while banning member', $exc->getMessage(), $exc->getTraceAsString()], 'podium');

            return PodiumResponse::error();
        }
    }

    public function afterBan(): void
    {
        $this->trigger(self::EVENT_AFTER_BANNING, new BanEvent(['model' => $this]));
    }

    public function beforeUnban(): bool
    {
        $event = new BanEvent();
        $this->trigger(self::EVENT_BEFORE_UNBANNING, $event);

        return $event->canUnban;
    }

    public function unban($id): PodiumResponse
    {
        if (!$this->beforeUnban()) {
            return PodiumResponse::error();
        }

        try {
            $member = $this->getMember();
            if (!$member->fetchOne($id)) {
                return PodiumResponse::error(['api' => Yii::t('podium.error', 'member.not.exists')]);
            }
            if (!$member->unban()) {
                return PodiumResponse::error($member->getErrors());
            }

            $this->afterUnban();

            return PodiumResponse::success();
        } catch (Throwable $exc) {
            Yii::error(['Exception while unbanning member', $exc->getMessage(), $exc->getTraceAsString()], 'podium');

            return PodiumResponse::error();
        }
    }

    public function afterUnban(): void
    {
        $this->trigger(self::EVENT_AFTER_UNBANNING, new BanEvent(['model' => $this]));
    }
}
