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

    /**
     * @throws InvalidConfigException
     */
    public function getMessenger(): MessengerInterface
    {
        /** @var MessengerInterface $messenger */
        $messenger = Instance::ensure($this->messengerConfig, MessengerInterface::class);

        return $messenger;
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

    /**
     * @throws InvalidConfigException
     */
    public function getRemover(): MessageRemoverInterface
    {
        /** @var MessageRemoverInterface $remover */
        $remover = Instance::ensure($this->removerConfig, MessageRemoverInterface::class);

        return $remover;
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

    /**
     * @throws InvalidConfigException
     */
    public function getArchiver(): MessageArchiverInterface
    {
        /** @var MessageArchiverInterface $archiver */
        $archiver = Instance::ensure($this->archiverConfig, MessageArchiverInterface::class);

        return $archiver;
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
