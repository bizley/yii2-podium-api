<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

/**
 * Interface AuthoredFormInterface
 * @package bizley\podium\api\interfaces
 */
interface AuthoredFormInterface extends ModelFormInterface
{
    /**
     * @param MembershipInterface $author
     */
    public function setAuthor(MembershipInterface $author): void;
}
