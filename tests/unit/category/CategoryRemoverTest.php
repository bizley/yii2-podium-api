<?php

declare(strict_types=1);

namespace bizley\podium\tests\unit\category;

use bizley\podium\api\interfaces\CategoryRepositoryInterface;
use bizley\podium\api\interfaces\RepositoryInterface;
use bizley\podium\api\services\category\CategoryRemover;
use Exception;
use PHPUnit\Framework\TestCase;

class CategoryRemoverTest extends TestCase
{
    private CategoryRemover $service;

    protected function setUp(): void
    {
        $this->service = new CategoryRemover();
    }

    public function testBeforeRemoveShouldReturnTrue(): void
    {
        self::assertTrue($this->service->beforeRemove());
    }

    public function testRemoveShouldReturnErrorWhenRepositoryIsWrong(): void
    {
        $result = $this->service->remove($this->createMock(RepositoryInterface::class));

        self::assertFalse($result->getResult());
        self::assertEmpty($result->getErrors());
    }

    public function testRemoveShouldReturnErrorWhenRemovingErrored(): void
    {
        $category = $this->createMock(CategoryRepositoryInterface::class);
        $category->method('isArchived')->willReturn(true);
        $category->method('delete')->willReturn(false);
        $result = $this->service->remove($category);

        self::assertFalse($result->getResult());
        self::assertEmpty($result->getErrors());
    }

    public function testRemoveShouldReturnErrorWhenCategoryIsNotArchived(): void
    {
        $category = $this->createMock(CategoryRepositoryInterface::class);
        $category->method('isArchived')->willReturn(false);
        $category->method('delete')->willReturn(true);
        $result = $this->service->remove($category);

        self::assertFalse($result->getResult());
        self::assertSame('category.must.be.archived', $result->getErrors()['api']);
    }

    public function testRemoveShouldReturnSuccessWhenRemovingIsDone(): void
    {
        $category = $this->createMock(CategoryRepositoryInterface::class);
        $category->method('isArchived')->willReturn(true);
        $category->method('delete')->willReturn(true);
        $result = $this->service->remove($category);

        self::assertTrue($result->getResult());
    }

    public function testRemoveShouldReturnErrorWhenRemovingThrowsException(): void
    {
        $category = $this->createMock(CategoryRepositoryInterface::class);
        $category->method('isArchived')->willReturn(true);
        $category->method('delete')->willThrowException(new Exception('exc'));
        $result = $this->service->remove($category);

        self::assertFalse($result->getResult());
        self::assertSame('exc', $result->getErrors()['exception']->getMessage());
    }

    public function testRemoveShouldReturnErrorWhenIsArchivedThrowsException(): void
    {
        $category = $this->createMock(CategoryRepositoryInterface::class);
        $category->method('isArchived')->willThrowException(new Exception('exc'));
        $result = $this->service->remove($category);

        self::assertFalse($result->getResult());
        self::assertSame('exc', $result->getErrors()['exception']->getMessage());
    }
}
