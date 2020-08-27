<?php

declare(strict_types=1);

namespace bizley\podium\api\components;

use bizley\podium\api\interfaces\MemberRepositoryInterface;
use bizley\podium\api\interfaces\MembershipInterface;
use bizley\podium\api\interfaces\MessageArchiverInterface;
use bizley\podium\api\interfaces\MessageInterface;
use bizley\podium\api\interfaces\MessageParticipantModelInterface;
use bizley\podium\api\interfaces\MessageRemoverInterface;
use bizley\podium\api\interfaces\MessengerInterface;
use bizley\podium\api\models\message\MessageArchiver;
use bizley\podium\api\models\message\MessageMessenger;
use bizley\podium\api\models\message\MessageRemover;
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



    public function getMessenger(): MessengerInterface
    {
        return new $this->messengerConfig();
    }

    /**
     * Sends message.
     */
    public function send(
        array $data,
        MembershipInterface $sender,
        MembershipInterface $receiver,
        MessageParticipantModelInterface $replyTo = null // TODO: Check if this should be Message instead
    ): PodiumResponse {
        $sending = $this->getMessenger();

        $sending->setSender($sender);
        $sending->setReceiver($receiver);
        $sending->setReplyTo($replyTo);

        if (!$sending->loadData($data)) {
            return PodiumResponse::error();
        }

        return $sending->send();
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
     */
    public function remove(MessageRepositoryInterface $message, MemberRepositoryInterface $participant): PodiumResponse
    {
        return $this->getRemover()->remove($message, $participant);
    }

    public function getArchiver(int $id, MembershipInterface $participant): ?MessageArchiverInterface
    {
        $handler = $this->archiverConfig;

        return $handler::findByMessageIdAndParticipant($id, $participant);
    }

    /**
     * Archives message copy.
     *
     * @throws ModelNotFoundException
     */
    public function archive(int $id, MembershipInterface $participant): PodiumResponse
    {
        $messageArchiver = $this->getArchiver($id, $participant);

        if (null === $messageArchiver) {
            throw new ModelNotFoundException('Message copy of given ID and side can not be found.');
        }

        return $messageArchiver->archive();
    }

    /**
     * Revives message copy.
     *
     * @throws ModelNotFoundException
     */
    public function revive(int $id, MembershipInterface $participant): PodiumResponse
    {
        $messageArchiver = $this->getArchiver($id, $participant);

        if (null === $messageArchiver) {
            throw new ModelNotFoundException('Message copy of given ID and side can not be found.');
        }

        return $messageArchiver->revive();
    }
}
