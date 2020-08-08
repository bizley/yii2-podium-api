<?php

declare(strict_types=1);

namespace bizley\podium\api\components;

/**
 * Class PodiumResponse
 * @package bizley\podium\api\base
 */
final class PodiumResponse
{
    private bool $result;
    private array $errors;
    private array $data;

    private function __construct(bool $result, array $errors = [], array $data = [])
    {
        $this->result = $result;
        $this->errors = $errors;
        $this->data = $data;
    }

    /**
     * Returns successful response.
     * @param array $data
     * @return PodiumResponse
     */
    public static function success(array $data = []): PodiumResponse
    {
        return new self(true, [], $data);
    }

    /**
     * Returns erroneous response.
     * @param array $errors
     * @return PodiumResponse
     */
    public static function error(array $errors = []): PodiumResponse
    {
        return new self(false, $errors);
    }

    public function getResult(): bool
    {
        return $this->result;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getData(): array
    {
        return $this->data;
    }
}
