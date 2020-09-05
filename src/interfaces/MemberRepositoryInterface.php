<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

interface MemberRepositoryInterface extends RepositoryInterface
{
    public function ban(): bool;

    public function unban(): bool;

    /**
     * @param int|string|array $id
     */
    public function register($id, array $data = []): bool;

    public function activate(): bool;
}
