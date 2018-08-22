<?php

declare(strict_types=1);

namespace bizley\podium\api\base;

use bizley\podium\api\interfaces\ArchivableInterface;
use bizley\podium\api\interfaces\MembershipInterface;
use bizley\podium\api\interfaces\MessageFormInterface;
use bizley\podium\api\interfaces\MessageInterface;
use bizley\podium\api\interfaces\ModelInterface;
use bizley\podium\api\interfaces\RemovableInterface;
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
     * @var string|array|ModelInterface
     * Component ID, class, configuration array, or instance of ModelInterface.
     */
    public $messageHandler = \bizley\podium\api\models\message\Message::class;

    /**
     * @var string|array|MessageFormInterface
     * Component ID, class, configuration array, or instance of MessageFormInterface.
     */
    public $messageFormHandler = \bizley\podium\api\models\message\MessageForm::class;

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function init(): void
    {
        parent::init();

        $this->messageHandler = Instance::ensure($this->messageHandler, ModelInterface::class);
        $this->messageFormHandler = Instance::ensure($this->messageFormHandler, MessageFormInterface::class);
    }

    /**
     * @param int $id
     * @return ModelInterface|null
     */
    public function getMessageById(int $id): ?ModelInterface
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
    public function getMessages(?DataFilter $filter = null, $sort = null, $pagination = null): DataProviderInterface
    {
        $messageClass = $this->messageHandler;
        return $messageClass::findByFilter($filter, $sort, $pagination);
    }

    /**
     * @return MessageFormInterface
     */
    public function getMessageForm(): MessageFormInterface
    {
        return new $this->messageFormHandler;
    }

    /**
     * @param array $data
     * @param MembershipInterface $sender
     * @param MembershipInterface $receiver
     * @return PodiumResponse
     */
    public function create(array $data, MembershipInterface $sender, MembershipInterface $receiver): PodiumResponse
    {
        $messageForm = $this->getMessageForm();
        $messageForm->setSender($sender);
        $messageForm->setReceiver($receiver);

        if (!$messageForm->loadData($data)) {
            return PodiumResponse::error();
        }
        return $messageForm->create();
    }

    /**
     * @param RemovableInterface $messageRemover
     * @return PodiumResponse
     */
    public function remove(RemovableInterface $messageRemover): PodiumResponse
    {
        return $messageRemover->remove();
    }

    /**
     * @param ArchivableInterface $postArchiver
     * @return PodiumResponse
     */
    public function archive(ArchivableInterface $postArchiver): PodiumResponse
    {
        return $postArchiver->archive();
    }

    /**
     * @param ArchivableInterface $postArchiver
     * @return PodiumResponse
     */
    public function revive(ArchivableInterface $postArchiver): PodiumResponse
    {
        return $postArchiver->revive();
    }
}
