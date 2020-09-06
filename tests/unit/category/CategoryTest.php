<?php

declare(strict_types=1);

namespace bizley\podium\tests\unit\category;

use bizley\podium\api\ars\CategoryActiveRecord;
use bizley\podium\api\components\Category;
use bizley\podium\api\interfaces\ARCategoryRepositoryInterface;
use PHPUnit\Framework\TestCase;
use yii\base\InvalidConfigException;
use yii\data\ActiveDataProvider;

class CategoryTest extends TestCase
{
    private Category $component;

    protected function setUp(): void
    {
        $this->component = new Category();
    }

    public function testGetByIdShouldThrowExceptionWhenRepositoryIsMisconfigured(): void
    {
        $this->expectException(InvalidConfigException::class);

        $this->component->repositoryConfig = '';

        $this->component->getById(1);
    }

    public function testGetByIdShouldReturnNullWhenModelDoesntExist(): void
    {
        $categoryRepository = $this->createMock(ARCategoryRepositoryInterface::class);
        $categoryRepository->method('fetchOne')->willReturn(false);
        $categoryRepository->method('getModel')->willReturn(new CategoryActiveRecord());

        $this->component->repositoryConfig = $categoryRepository;

        self::assertNull($this->component->getById(1));
    }

    public function testGetByIdShouldReturnCategoryARWhenModelExists(): void
    {
        $categoryRepository = $this->createMock(ARCategoryRepositoryInterface::class);
        $categoryRepository->method('fetchOne')->willReturn(true);
        $categoryRepository->method('getModel')->willReturn(new CategoryActiveRecord());

        $this->component->repositoryConfig = $categoryRepository;

        self::assertInstanceOf(CategoryActiveRecord::class, $this->component->getById(1));
    }

    public function testGetAllShouldThrowExceptionWhenRepositoryIsMisconfigured(): void
    {
        $this->expectException(InvalidConfigException::class);

        $this->component->repositoryConfig = '';

        $this->component->getAll();
    }

    public function testGetAllShouldReturnDataProvider(): void
    {
        $categoryRepository = $this->createMock(ARCategoryRepositoryInterface::class);
        $categoryRepository->method('fetchOne')->willReturn(true);
        $categoryRepository->method('getCollection')->willReturn(new ActiveDataProvider());

        $this->component->repositoryConfig = $categoryRepository;

        self::assertInstanceOf(ActiveDataProvider::class, $this->component->getAll());
    }
}
