<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

/**
 * Interface CategoryFormInterface
 * @package bizley\podium\api\interfaces
 */
interface CategoryFormInterface
{
    /**
     * @param MembershipInterface $author
     */
    public function setAuthor(MembershipInterface $author): void;

    /**
     * Loads form data.
     * @param array|null $data form data
     * @return bool
     */
    public function loadData(?array $data = null): bool;

    /**
     * Creates new category.
     * @return bool
     */
    public function create(): bool;

    /**
     * Updates category.
     * @return bool
     */
    public function edit(): bool;
}
