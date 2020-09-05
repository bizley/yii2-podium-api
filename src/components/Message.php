<?php

declare(strict_types=1);

namespace bizley\podium\api\components;

use bizley\podium\api\interfaces\MemberRepositoryInterface;
use bizley\podium\api\interfaces\MessageArchiverInterface;
use bizley\podium\api\interfaces\MessageInterface;
use bizley\podium\api\interfaces\MessageRemoverInterface;
use bizley\podium\api\interfaces\MessageRepositoryInterface;
use bizley\podium\api\interfaces\MessengerInterface;
use bizley\podium\api\services\message\MessageArchiver;
use bizley\podium\api\services\message\MessageMessenger;
use bizley\podium\api\services\message\MessageRemover;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\di\Instance;

final class Message extends Component implements MessageInterface
{
    /**
     * @var string|array|MessengerInterface
     */
    public $messengerConfig = MessageMessenger::class;

    /**
     * @var string|array|MessageRemoverInterface
     */
    public $removerConfig = MessageRemover::class;

    /**
     * @var string|array|MessageArchiverInterface
     */
    public $archiverConfig = MessageArchiver::class;

    private ?MessengerInterface $messenger = null;

    /**
     * @throws InvalidConfigException
     */
    public function getMessenger(): MessengerInterface
    {
        if (null === $this->messenger) {
            /** @var MessengerInterface $messenger */
            $messenger = Instance::ensure($this->messengerConfig, MessengerInterface::class);
            $this->messenger = $messenger;
        }

        return $this->messenger;
    }

    /**
     * Sends message.
     *
     * @throws InvalidConfigException
     */
    public function send(
        MemberRepositoryInterface $sender,
        MemberRepositoryInterface $receiver,
        MessageRepositoryInterface $replyTo = null,
        array $data = []
    ): PodiumResponse {
        return $this->getMessenger()->send($sender, $receiver, $replyTo, $data);
    }

    private ?MessageRemoverInterface $remover = null;

    /**
     * @throws InvalidConfigException
     */
    public function getRemover(): MessageRemoverInterface
    {
        if (null === $this->remover) {
            /** @var MessageRemoverInterface $remover */
            $remover = Instance::ensure($this->removerConfig, MessageRemoverInterface::class);
            $this->remover = $remover;
        }

        return $this->remover;
    }

    /**
     * Deletes message copy.
     *
     * @throws InvalidConfigException
     */
    public function remove(MessageRepositoryInterface $message, MemberRepositoryInterface $participant): PodiumResponse
    {
        return $this->getRemover()->remove($message, $participant);
    }

    private ?MessageArchiverInterface $archiver = null;

    /**
     * @throws InvalidConfigException
     */
    public function getArchiver(): MessageArchiverInterface
    {
        if (null === $this->archiver) {
            /** @var MessageArchiverInterface $archiver */
            $archiver = Instance::ensure($this->archiverConfig, MessageArchiverInterface::class);
            $this->archiver = $archiver;
        }

        return $this->archiver;
    }

    /**
     * Archives message copy.
     *
     * @throws InvalidConfigException
     */
    public function archive(MessageRepositoryInterface $message, MemberRepositoryInterface $participant): PodiumResponse
    {
        return $this->getArchiver()->archive($message, $participant);
    }

    /**
     * Revives message copy.
     *
     * @throws InvalidConfigException
     */
    public function revive(MessageRepositoryInterface $message, MemberRepositoryInterface $participant): PodiumResponse
    {
        return $this->getArchiver()->revive($message, $participant);
    }
}
