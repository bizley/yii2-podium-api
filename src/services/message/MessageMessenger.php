<?php

declare(strict_types=1);

namespace bizley\podium\api\services\message;

use bizley\podium\api\components\PodiumResponse;
use bizley\podium\api\events\ArchiveEvent;
use bizley\podium\api\interfaces\MemberRepositoryInterface;
use bizley\podium\api\interfaces\MessageRepositoryInterface;
use bizley\podium\api\interfaces\MessengerInterface;
use bizley\podium\api\repositories\MessageRepository;
use Throwable;
use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\db\Transaction;
use yii\di\Instance;

final class MessageMessenger extends Component implements MessengerInterface
{
    public const EVENT_BEFORE_SENDING = 'podium.message.sending.before';
    public const EVENT_AFTER_SENDING = 'podium.message.sending.after';

    private ?MessageRepositoryInterface $message = null;

    /**
     * @var string|array|MessageRepositoryInterface
     */
    public $repositoryConfig = MessageRepository::class;

    /**
     * @throws InvalidConfigException
     */
    private function getMessage(): MessageRepositoryInterface
    {
        if (null === $this->message) {
            /** @var MessageRepositoryInterface $message */
            $message = Instance::ensure($this->repositoryConfig, MessageRepositoryInterface::class);
            $this->message = $message;
        }

        return $this->message;
    }

    public function beforeSend(): bool
    {
        $event = new ArchiveEvent();
        $this->trigger(self::EVENT_BEFORE_SENDING, $event);

        return $event->canArchive;
    }

    /**
     * Sends the message.
     */
    public function send(
        MemberRepositoryInterface $sender,
        MemberRepositoryInterface $receiver,
        MessageRepositoryInterface $replyTo = null,
        array $data = []
    ): PodiumResponse {
        if (!$this->beforeSend()) {
            return PodiumResponse::error();
        }

        /** @var Transaction $transaction */
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $message = $this->getMessage();

            if (!$message->send($sender, $receiver, $replyTo, $data)) {
                return PodiumResponse::error($message->getErrors());
            }

            $this->afterSend($message);
            $transaction->commit();

            return PodiumResponse::success();
        } catch (Throwable $exc) {
            $transaction->rollBack();
            Yii::error(['Exception while sending message', $exc->getMessage(), $exc->getTraceAsString()], 'podium');

            return PodiumResponse::error();
        }
    }

    public function afterSend(MessageRepositoryInterface $message): void
    {
        $this->trigger(self::EVENT_AFTER_SENDING, new ArchiveEvent(['repository' => $message]));
    }
}
