<?php

declare(strict_types=1);

namespace bizley\podium\api\services\message;

use bizley\podium\api\components\PodiumResponse;
use bizley\podium\api\events\ArchiveEvent;
use bizley\podium\api\interfaces\MemberRepositoryInterface;
use bizley\podium\api\interfaces\MessageArchiverInterface;
use bizley\podium\api\interfaces\MessageRepositoryInterface;
use Throwable;
use Yii;
use yii\base\Component;

final class MessageArchiver extends Component implements MessageArchiverInterface
{
    public const EVENT_BEFORE_ARCHIVING = 'podium.message.archiving.before';
    public const EVENT_AFTER_ARCHIVING = 'podium.message.archiving.after';
    public const EVENT_BEFORE_REVIVING = 'podium.message.reviving.before';
    public const EVENT_AFTER_REVIVING = 'podium.message.reviving.after';

    public function beforeArchive(): bool
    {
        $event = new ArchiveEvent();
        $this->trigger(self::EVENT_BEFORE_ARCHIVING, $event);

        return $event->canArchive;
    }

    /**
     * Archives the message.
     */
    public function archive(MessageRepositoryInterface $message, MemberRepositoryInterface $participant): PodiumResponse
    {
        if (!$this->beforeArchive()) {
            return PodiumResponse::error();
        }

        try {
            $messageSide = $message->getParticipant($participant);

            if ($messageSide->isArchived()) {
                return PodiumResponse::error(['api' => Yii::t('podium.error', 'message.already.archived')]);
            }

            if (!$messageSide->archive()) {
                return PodiumResponse::error($messageSide->getErrors());
            }

            $this->afterArchive($message);

            return PodiumResponse::success();
        } catch (Throwable $exc) {
            Yii::error(['Exception while archiving message', $exc->getMessage(), $exc->getTraceAsString()], 'podium');

            return PodiumResponse::error();
        }
    }

    public function afterArchive(MessageRepositoryInterface $message): void
    {
        $this->trigger(self::EVENT_AFTER_ARCHIVING, new ArchiveEvent(['repository' => $message]));
    }

    public function beforeRevive(): bool
    {
        $event = new ArchiveEvent();
        $this->trigger(self::EVENT_BEFORE_REVIVING, $event);

        return $event->canRevive;
    }

    /**
     * Revives the thread.
     */
    public function revive(MessageRepositoryInterface $message, MemberRepositoryInterface $participant): PodiumResponse
    {
        if (!$this->beforeRevive()) {
            return PodiumResponse::error();
        }

        try {
            $messageSide = $message->getParticipant($participant);

            if (!$messageSide->isArchived()) {
                return PodiumResponse::error(['api' => Yii::t('podium.error', 'message.not.archived')]);
            }

            if (!$messageSide->revive()) {
                return PodiumResponse::error($messageSide->getErrors());
            }

            $this->afterRevive($message);

            return PodiumResponse::success();
        } catch (Throwable $exc) {
            Yii::error(['Exception while reviving message', $exc->getMessage(), $exc->getTraceAsString()], 'podium');

            return PodiumResponse::error();
        }
    }

    public function afterRevive(MessageRepositoryInterface $message): void
    {
        $this->trigger(self::EVENT_AFTER_REVIVING, new ArchiveEvent(['repository' => $message]));
    }
}
