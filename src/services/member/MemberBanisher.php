<?php

declare(strict_types=1);

namespace bizley\podium\api\services\member;

use bizley\podium\api\components\PodiumResponse;
use bizley\podium\api\events\BanEvent;
use bizley\podium\api\interfaces\BanisherInterface;
use bizley\podium\api\interfaces\MemberRepositoryInterface;
use Throwable;
use Yii;
use yii\base\Component;

final class MemberBanisher extends Component implements BanisherInterface
{
    public const EVENT_BEFORE_BANNING = 'podium.member.banning.before';
    public const EVENT_AFTER_BANNING = 'podium.member.banning.after';
    public const EVENT_BEFORE_UNBANNING = 'podium.member.unbanning.before';
    public const EVENT_AFTER_UNBANNING = 'podium.member.unbanning.after';

    public function beforeBan(): bool
    {
        $event = new BanEvent();
        $this->trigger(self::EVENT_BEFORE_BANNING, $event);

        return $event->canBan;
    }

    public function ban(MemberRepositoryInterface $member): PodiumResponse
    {
        if (!$this->beforeBan()) {
            return PodiumResponse::error();
        }

        try {
            if (!$member->ban()) {
                return PodiumResponse::error($member->getErrors());
            }

            $this->afterBan($member);

            return PodiumResponse::success();
        } catch (Throwable $exc) {
            Yii::error(['Exception while banning member', $exc->getMessage(), $exc->getTraceAsString()], 'podium');

            return PodiumResponse::error();
        }
    }

    public function afterBan(MemberRepositoryInterface $member): void
    {
        $this->trigger(self::EVENT_AFTER_BANNING, new BanEvent(['repository' => $member]));
    }

    public function beforeUnban(): bool
    {
        $event = new BanEvent();
        $this->trigger(self::EVENT_BEFORE_UNBANNING, $event);

        return $event->canUnban;
    }

    public function unban(MemberRepositoryInterface $member): PodiumResponse
    {
        if (!$this->beforeUnban()) {
            return PodiumResponse::error();
        }

        try {
            if (!$member->unban()) {
                return PodiumResponse::error($member->getErrors());
            }

            $this->afterUnban($member);

            return PodiumResponse::success();
        } catch (Throwable $exc) {
            Yii::error(['Exception while unbanning member', $exc->getMessage(), $exc->getTraceAsString()], 'podium');

            return PodiumResponse::error();
        }
    }

    public function afterUnban(MemberRepositoryInterface $member): void
    {
        $this->trigger(self::EVENT_AFTER_UNBANNING, new BanEvent(['repository' => $member]));
    }
}
