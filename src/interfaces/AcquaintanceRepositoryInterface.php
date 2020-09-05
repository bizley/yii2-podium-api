<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

interface AcquaintanceRepositoryInterface
{
    public function fetchOne(MemberRepositoryInterface $member, MemberRepositoryInterface $target): bool;

    public function prepare(MemberRepositoryInterface $member, MemberRepositoryInterface $target): void;

    public function getErrors(): array;

    public function delete(): bool;

    public function befriend(): bool;

    public function ignore(): bool;

    public function isFriend(): bool;

    public function isIgnoring(): bool;
}
