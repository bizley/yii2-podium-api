<?php

declare(strict_types=1);

namespace bizley\podium\api\base;

use bizley\podium\api\interfaces\ArchivableInterface;
use bizley\podium\api\interfaces\MembershipInterface;
use bizley\podium\api\interfaces\MessageInterface;
use bizley\podium\api\interfaces\MessageParticipantModelInterface;
use bizley\podium\api\interfaces\MessageRemoverInterface;
use bizley\podium\api\interfaces\ModelInterface;
use bizley\podium\api\interfaces\RemoverInterface;
use bizley\podium\api\interfaces\SendingInterface;
use yii\data\DataFilter;
use yii\data\DataProviderInterface;
use yii\data\Pagination;
use yii\data\Sort;
use yii\di\Instance;

/**
 * Class Message
 * @package bizley\podium\api\base
 */
class Message extends PodiumComponent implements MessageInterface
{
    /**
     * @var string|array|ModelInterface message handler
     * Component ID, class, configuration array, or instance of ModelInterface.
     */
    public $messageHandler = \bizley\podium\api\models\message\Message::class;

    /**
     * @var string|array|SendingInterface message mailer handler
     * Component ID, class, configuration array, or instance of SendingInterface.
     */
    public $mailerHandler = \bizley\podium\api\models\message\MessageMailer::class;

    /**
     * @var string|array|MessageRemoverInterface message remover handler
     * Component ID, class, configuration array, or instance of MessageRemoverInterface.
     */
    public $removerHandler = \bizley\podium\api\models\message\MessageRemover::class;

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function init(): void
    {
        parent::init();

        $this->messageHandler = Instance::ensure($this->messageHandler, ModelInterface::class);
        $this->mailerHandler = Instance::ensure($this->mailerHandler, SendingInterface::class);
        $this->removerHandler = Instance::ensure($this->removerHandler, MessageRemoverInterface::class);
    }

    /**
     * @param int $id
     * @return ModelInterface|null
     */
    public function getById(int $id): ?ModelInterface
    {
        $messageClass = $this->messageHandler;

        return $messageClass::findById($id);
    }

    /**
     * @param null|DataFilter $filter
     * @param null|bool|array|Sort $sort
     * @param null|bool|array|Pagination $pagination
     * @return DataProviderInterface
     */
    public function getAll(?DataFilter $filter = null, $sort = null, $pagination = null): DataProviderInterface
    {
        $messageClass = $this->messageHandler;

        return $messageClass::findByFilter($filter, $sort, $pagination);
    }

    /**
     * @return SendingInterface
     */
    public function getMailer(): SendingInterface
    {
        return new $this->mailerHandler;
    }

    /**
     * Sends message.
     * @param array $data
     * @param MembershipInterface $sender
     * @param MembershipInterface $receiver
     * @param MessageParticipantModelInterface $replyTo
     * @return PodiumResponse
     */
    public function send(
        array $data,
        MembershipInterface $sender,
        MembershipInterface $receiver,
        ?MessageParticipantModelInterface $replyTo = null
    ): PodiumResponse
    {
        $sending = $this->getMailer();

        $sending->setSender($sender);
        $sending->setReceiver($receiver);
        $sending->setReplyTo($replyTo);

        if (!$sending->loadData($data)) {
            return PodiumResponse::error();
        }

        return $sending->send();
    }

    /**
     * @param int $id
     * @param string $side
     * @return MessageRemoverInterface|null
     */
    public function getRemover(int $id, string $side): ?MessageRemoverInterface
    {
        $handler = $this->removerHandler;

        return $handler::findByMessageIdAndSide($id, $side);
    }

    /**
     * Deletes message copy.
     * @param int $id
     * @param string $side
     * @return PodiumResponse
     * @throws ModelNotFoundException
     */
    public function remove(int $id, string $side): PodiumResponse
    {
        $messageRemover = $this->getRemover($id, $side);

        if ($messageRemover === null) {
            throw new ModelNotFoundException('Message copy of given ID and side can not be found.');
        }

        $messageRemover->setMessageHandler($this->messageHandler);

        return $messageRemover->remove();
    }

    /**
     * Archives message copy.
     * @param ArchivableInterface $messageParticipantArchiver
     * @return PodiumResponse
     */
    public function archive(ArchivableInterface $messageParticipantArchiver): PodiumResponse
    {
        return $messageParticipantArchiver->archive();
    }

    /**
     * Revives message copy.
     * @param ArchivableInterface $messageParticipantArchiver
     * @return PodiumResponse
     */
    public function revive(ArchivableInterface $messageParticipantArchiver): PodiumResponse
    {
        return $messageParticipantArchiver->revive();
    }
}
