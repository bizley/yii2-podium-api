<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

interface ThumbRepositoryInterface
{
    public function fetchOne($memberId, $postId): bool;
    public function getErrors(): array;
    public function isUp(): bool;
    public function isDown(): bool;
    public function prepare($memberId, $postId): void;
    public function up(): bool;
    public function down(): bool;
    public function reset(): bool;
}
