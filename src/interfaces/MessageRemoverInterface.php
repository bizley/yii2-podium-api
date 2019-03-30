<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

/**
 * Interface RemovableInterface
 * @package bizley\podium\api\interfaces
 */
interface MessageRemoverInterface extends RemoverInterface
{
    /**
     * @param int $messageId
     * @param string $side
     * @return RemoverInterface|null
     */
    public static function findByMessageIdAndSide(int $messageId, string $side): ?MessageRemoverInterface;

    /**
     * @param ModelInterface $messageHandler
     */
    public function setMessageHandler(ModelInterface $messageHandler): void;
}
