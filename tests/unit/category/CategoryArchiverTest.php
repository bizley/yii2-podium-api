<?php

declare(strict_types=1);

namespace bizley\podium\tests\unit\category;

use bizley\podium\api\interfaces\CategoryRepositoryInterface;
use bizley\podium\api\interfaces\RepositoryInterface;
use bizley\podium\api\services\category\CategoryArchiver;
use Exception;
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

    public function testArchiveShouldReturnErrorWhenArchivingErrored(): void
    {
        $category = $this->createMock(CategoryRepositoryInterface::class);
        $category->method('archive')->willReturn(false);
        $category->method('getErrors')->willReturn([1]);
        $result = $this->service->archive($category);

        self::assertFalse($result->getResult());
        self::assertSame([1], $result->getErrors());
    }

    public function testArchiveShouldReturnSuccessWhenArchivingIsDone(): void
    {
        $category = $this->createMock(CategoryRepositoryInterface::class);
        $category->method('archive')->willReturn(true);
        $result = $this->service->archive($category);

        self::assertTrue($result->getResult());
    }

    public function testArchiveShouldReturnErrorWhenArchivingThrowsException(): void
    {
        $category = $this->createMock(CategoryRepositoryInterface::class);
        $category->method('archive')->willThrowException(new Exception('exc'));
        $result = $this->service->archive($category);

        self::assertFalse($result->getResult());
        self::assertSame('exc', $result->getErrors()['exception']->getMessage());
    }

    public function testBeforeReviveShouldReturnTrue(): void
    {
        self::assertTrue($this->service->beforeRevive());
    }

    public function testReviveShouldReturnErrorWhenRepositoryIsWrong(): void
    {
        $result = $this->service->revive($this->createMock(RepositoryInterface::class));

        self::assertFalse($result->getResult());
        self::assertEmpty($result->getErrors());
    }

    public function testReviveShouldReturnErrorWhenRevivingErrored(): void
    {
        $category = $this->createMock(CategoryRepositoryInterface::class);
        $category->method('revive')->willReturn(false);
        $category->method('getErrors')->willReturn([1]);
        $result = $this->service->revive($category);

        self::assertFalse($result->getResult());
        self::assertSame([1], $result->getErrors());
    }

    public function testReviveShouldReturnSuccessWhenRevivingIsDone(): void
    {
        $category = $this->createMock(CategoryRepositoryInterface::class);
        $category->method('revive')->willReturn(true);
        $result = $this->service->revive($category);

        self::assertTrue($result->getResult());
    }

    public function testReviveShouldReturnErrorWhenRevivingThrowsException(): void
    {
        $category = $this->createMock(CategoryRepositoryInterface::class);
        $category->method('revive')->willThrowException(new Exception('exc'));
        $result = $this->service->revive($category);

        self::assertFalse($result->getResult());
        self::assertSame('exc', $result->getErrors()['exception']->getMessage());
    }
}
