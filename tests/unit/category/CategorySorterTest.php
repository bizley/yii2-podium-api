<?php

declare(strict_types=1);

namespace bizley\podium\tests\unit\category;

use bizley\podium\api\interfaces\CategoryRepositoryInterface;
use bizley\podium\api\interfaces\RepositoryInterface;
use bizley\podium\api\services\category\CategorySorter;
use bizley\podium\tests\AppTestCase;
use Exception;
use Yii;
use yii\db\Connection;
use yii\db\Transaction;

class CategorySorterTest extends AppTestCase
{
    private CategorySorter $service;

    protected function setUp(): void
    {
        $this->service = new CategorySorter();
        $connection = $this->createMock(Connection::class);
        $connection->method('beginTransaction')->willReturn($this->createMock(Transaction::class));
        Yii::$app->set('db', $connection);
    }

    public function testBeforeReplaceShouldReturnTrue(): void
    {
        self::assertTrue($this->service->beforeReplace());
    }

    public function testReplaceShouldReturnErrorWhenFirstRepositoryIsWrong(): void
    {
        $result = $this->service->replace(
            $this->createMock(RepositoryInterface::class),
            $this->createMock(CategoryRepositoryInterface::class)
        );

        self::assertFalse($result->getResult());
        self::assertEmpty($result->getErrors());
    }

    public function testReplaceShouldReturnErrorWhenSecondRepositoryIsWrong(): void
    {
        $result = $this->service->replace(
            $this->createMock(CategoryRepositoryInterface::class),
            $this->createMock(RepositoryInterface::class)
        );

        self::assertFalse($result->getResult());
        self::assertEmpty($result->getErrors());
    }

    public function testReplaceShouldReturnErrorWhenSettingFirstOrderErrored(): void
    {
        $category = $this->createMock(CategoryRepositoryInterface::class);
        $category->method('getOrder')->willReturn(1);
        $category->method('setOrder')->willReturn(false);
        $result = $this->service->replace($category, $category);

        self::assertFalse($result->getResult());
    }

    public function testReplaceShouldReturnErrorWhenSettingSecondOrderErrored(): void
    {
        $category1 = $this->createMock(CategoryRepositoryInterface::class);
        $category1->method('getOrder')->willReturn(1);
        $category1->method('setOrder')->willReturn(true);
        $category2 = $this->createMock(CategoryRepositoryInterface::class);
        $category2->method('getOrder')->willReturn(2);
        $category2->method('setOrder')->willReturn(false);
        $result = $this->service->replace($category1, $category2);

        self::assertFalse($result->getResult());
    }

    public function testReplaceShouldReturnSuccessWhenReplacingIsDone(): void
    {
        $category = $this->createMock(CategoryRepositoryInterface::class);
        $category->method('getOrder')->willReturn(1);
        $category->method('setOrder')->willReturn(true);
        $result = $this->service->replace($category, $category);

        self::assertTrue($result->getResult());
    }

    public function testReplaceShouldReturnErrorWhenReplacingThrowsException(): void
    {
        $category = $this->createMock(CategoryRepositoryInterface::class);
        $category->method('getOrder')->willReturn(1);
        $category->method('setOrder')->willThrowException(new Exception('exc'));
        $result = $this->service->replace($category, $category);

        self::assertFalse($result->getResult());
        self::assertSame('exc', $result->getErrors()['exception']->getMessage());
    }

    public function testBeforeSortShouldReturnTrue(): void
    {
        self::assertTrue($this->service->beforeSort());
    }

    public function testSortShouldReturnErrorWhenSortingErrored(): void
    {
        $category = $this->createMock(CategoryRepositoryInterface::class);
        $category->method('sort')->willReturn(false);
        $this->service->repositoryConfig = $category;
        $result = $this->service->sort();

        self::assertFalse($result->getResult());
    }

    public function testSortShouldReturnSuccessWhenSortingIsDone(): void
    {
        $category = $this->createMock(CategoryRepositoryInterface::class);
        $category->method('sort')->willReturn(true);
        $this->service->repositoryConfig = $category;
        $result = $this->service->sort();

        self::assertTrue($result->getResult());
    }

    public function testSortShouldReturnErrorWhenSortingThrowsException(): void
    {
        $category = $this->createMock(CategoryRepositoryInterface::class);
        $category->method('sort')->willThrowException(new Exception('exc'));
        $this->service->repositoryConfig = $category;
        $result = $this->service->sort();

        self::assertFalse($result->getResult());
        self::assertSame('exc', $result->getErrors()['exception']->getMessage());
    }
}
