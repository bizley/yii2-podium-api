<?php

declare(strict_types=1);

namespace bizley\podium\tests\unit\category;

use bizley\podium\api\interfaces\RepositoryInterface;
use bizley\podium\api\services\category\CategoryArchiver;
use PHPUnit\Framework\TestCase;

class CategoryArchiverTest extends TestCase
{
    private CategoryArchiver $service;

    protected function setUp(): void
    {
        $this->service = new CategoryArchiver();
    }

    public function testBeforeArchiveShouldReturnTrue(): void
    {
        self::assertTrue($this->service->beforeArchive());
    }

    public function testArchiveShouldReturnErrorWhenRepositoryIsWrong(): void
    {
        $result = $this->service->archive($this->createMock(RepositoryInterface::class));
        self::assertFalse($result->getResult());
        self::assertEmpty($result->getErrors());
    }
}
