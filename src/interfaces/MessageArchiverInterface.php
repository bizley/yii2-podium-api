<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

/**
 * Interface MessageArchiverInterface
 * @package bizley\podium\api\interfaces
 */
interface MessageArchiverInterface extends ArchiverInterface
{
    /**
     * @param int $messageId
     * @param string $side
     * @return RemoverInterface|null
     */
    public static function findByMessageIdAndSide(int $messageId, string $side): ?MessageArchiverInterface;
}
