<?php

declare(strict_types=1);

namespace bizley\podium\api\models\member;

use bizley\podium\api\enums\MemberStatus;
use bizley\podium\api\events\BanEvent;
use bizley\podium\api\interfaces\BanInterface;
use bizley\podium\api\repos\MemberRepo;
use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * Class MemberForm
 * @package bizley\podium\api\models\member
 */
class MemberBan extends MemberRepo implements BanInterface
{
    public const EVENT_BEFORE_BANNING = 'podium.member.banning.before';
    public const EVENT_AFTER_BANNING = 'podium.member.banning.after';
    public const EVENT_BEFORE_UNBANNING = 'podium.member.unbanning.before';
    public const EVENT_AFTER_UNBANNING = 'podium.member.unbanning.after';

    /**
     * @return array
     */
    public function behaviors(): array
    {
        return [
            'timestamp' => TimestampBehavior::class,
        ];
    }

    /**
     * @return bool
     */
    public function beforeBan(): bool
    {
        $event = new BanEvent();
        $this->trigger(self::EVENT_BEFORE_BANNING, $event);

        return $event->canBan;
    }

    /**
     * @return bool
     */
    public function ban(): bool
    {
        if (!$this->beforeBan()) {
            return false;
        }
        if ($this->status_id === MemberStatus::BANNED) {
            $this->addError('status_id', Yii::t('podium.error', 'member.already.banned'));
            return false;
        }
        $this->status_id = MemberStatus::BANNED;

        if (!$this->save(false)) {
            Yii::error(['member.ban', $this->errors], 'podium');
            return false;
        }
        $this->afterBan();
        return true;
    }

    public function afterBan(): void
    {
        $this->trigger(self::EVENT_AFTER_BANNING, new BanEvent([
            'model' => $this
        ]));
    }

    /**
     * @return bool
     */
    public function beforeUnban(): bool
    {
        $event = new BanEvent();
        $this->trigger(self::EVENT_BEFORE_UNBANNING, $event);

        return $event->canUnban;
    }

    /**
     * @return bool
     */
    public function unban(): bool
    {
        if (!$this->beforeUnban()) {
            return false;
        }
        if ($this->status_id === MemberStatus::ACTIVE) {
            $this->addError('status_id', Yii::t('podium.error', 'member.already.active'));
            return false;
        }
        $this->status_id = MemberStatus::ACTIVE;

        if (!$this->save(false)) {
            Yii::error(['member.unban', $this->errors], 'podium');
            return false;
        }
        $this->afterUnban();
        return true;
    }

    public function afterUnban(): void
    {
        $this->trigger(self::EVENT_AFTER_UNBANNING, new BanEvent([
            'model' => $this
        ]));
    }
}
