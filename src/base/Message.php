<?php

declare(strict_types=1);

namespace bizley\podium\api\base;

use bizley\podium\api\interfaces\ArchivableInterface;
use bizley\podium\api\interfaces\CategorisedFormInterface;
use bizley\podium\api\interfaces\MembershipInterface;
use bizley\podium\api\interfaces\MessageInterface;
use bizley\podium\api\interfaces\ModelFormInterface;
use bizley\podium\api\interfaces\ModelInterface;
use bizley\podium\api\interfaces\RemovableInterface;
use yii\data\DataFilter;
use yii\data\DataProviderInterface;
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
     * @var string|array|ModelFormInterface
     * Component ID, class, configuration array, or instance of ModelFormInterface.
     */
    public $messageFormHandler = \bizley\podium\api\models\message\MessageForm::class;

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function init()
    {
        parent::init();

        $this->messageHandler = Instance::ensure($this->messageHandler, ModelInterface::class);
        $this->messageFormHandler = Instance::ensure($this->messageFormHandler, ModelFormInterface::class);
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
     * @param null $sort
     * @param null $pagination
     * @return DataProviderInterface
     */
    public function getMessages(?DataFilter $filter = null, $sort = null, $pagination = null): DataProviderInterface
    {
        $messageClass = $this->messageHandler;
        return $messageClass::findByFilter($filter, $sort, $pagination);
    }

    /**
     * @return CategorisedFormInterface
     */
    public function getMessageForm(): ModelFormInterface
    {
        return new $this->messageFormHandler;
    }

    /**
     * @param array $data
     * @param MembershipInterface $sender
     * @param MembershipInterface $receiver
     * @return bool
     */
    public function create(array $data, MembershipInterface $sender, MembershipInterface $receiver): bool
    {
        $messageForm = $this->getMessageForm();
        $messageForm->setSender($sender);
        $messageForm->setReceiver($receiver);

        if (!$messageForm->loadData($data)) {
            return false;
        }
        return $messageForm->create();
    }

    /**
     * @param RemovableInterface $messageRemover
     * @return bool
     */
    public function remove(RemovableInterface $messageRemover): bool
    {
        return $messageRemover->remove();
    }

    /**
     * @param ArchivableInterface $postArchiver
     * @return bool
     */
    public function archive(ArchivableInterface $postArchiver): bool
    {
        return $postArchiver->archive();
    }

    /**
     * @param ArchivableInterface $postArchiver
     * @return bool
     */
    public function revive(ArchivableInterface $postArchiver): bool
    {
        return $postArchiver->revive();
    }
}
