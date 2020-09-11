<?php

declare(strict_types=1);

namespace bizley\podium\tests\unit\category;

use bizley\podium\api\ars\CategoryActiveRecord;
use bizley\podium\api\components\Category;
use bizley\podium\api\components\PodiumResponse;
use bizley\podium\api\interfaces\ARCategoryRepositoryInterface;
use bizley\podium\api\interfaces\ArchiverInterface;
use bizley\podium\api\interfaces\CategoryBuilderInterface;
use bizley\podium\api\interfaces\CategoryRepositoryInterface;
use bizley\podium\api\interfaces\MemberRepositoryInterface;
use bizley\podium\api\interfaces\RemoverInterface;
use bizley\podium\api\interfaces\SorterInterface;
use PHPUnit\Framework\TestCase;
use yii\base\InvalidConfigException;

class CategoryComponentTest extends TestCase
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

    public function testGetBuilderShouldThrowExceptionWhenBuilderIsMisconfigured(): void
    {
        $this->expectException(InvalidConfigException::class);

        $this->component->builderConfig = '';

        $this->component->getBuilder();
    }

    public function testCreateShouldRunBuildersCreate(): void
    {
        $builder = $this->createMock(CategoryBuilderInterface::class);
        $builder->expects(self::once())->method('create')->willReturn(PodiumResponse::success());
        $this->component->builderConfig = $builder;

        $this->component->create($this->createMock(MemberRepositoryInterface::class));
    }

    public function testEditShouldRunBuildersEdit(): void
    {
        $builder = $this->createMock(CategoryBuilderInterface::class);
        $builder->expects(self::once())->method('edit')->willReturn(PodiumResponse::success());
        $this->component->builderConfig = $builder;

        $this->component->edit($this->createMock(CategoryRepositoryInterface::class));
    }

    public function testGetRemoverShouldThrowExceptionWhenRemoverIsMisconfigured(): void
    {
        $this->expectException(InvalidConfigException::class);

        $this->component->removerConfig = '';

        $this->component->getRemover();
    }

    public function testRemoveShouldRunRemoversRemove(): void
    {
        $remover = $this->createMock(RemoverInterface::class);
        $remover->expects(self::once())->method('remove')->willReturn(PodiumResponse::success());
        $this->component->removerConfig = $remover;

        $this->component->remove($this->createMock(CategoryRepositoryInterface::class));
    }

    public function testGetSorterShouldThrowExceptionWhenSorterIsMisconfigured(): void
    {
        $this->expectException(InvalidConfigException::class);

        $this->component->sorterConfig = '';

        $this->component->getSorter();
    }

    public function testReplaceShouldRunSortersReplace(): void
    {
        $sorter = $this->createMock(SorterInterface::class);
        $sorter->expects(self::once())->method('replace')->willReturn(PodiumResponse::success());
        $this->component->sorterConfig = $sorter;

        $category = $this->createMock(CategoryRepositoryInterface::class);
        $this->component->replace($category, $category);
    }

    public function testSortShouldRunSortersSort(): void
    {
        $sorter = $this->createMock(SorterInterface::class);
        $sorter->expects(self::once())->method('sort')->willReturn(PodiumResponse::success());
        $this->component->sorterConfig = $sorter;

        $this->component->sort();
    }

    public function testGetArchiverShouldThrowExceptionWhenArchiverIsMisconfigured(): void
    {
        $this->expectException(InvalidConfigException::class);

        $this->component->archiverConfig = '';

        $this->component->getArchiver();
    }

    public function testArchiveShouldRunArchiversArchive(): void
    {
        $archiver = $this->createMock(ArchiverInterface::class);
        $archiver->expects(self::once())->method('archive')->willReturn(PodiumResponse::success());
        $this->component->archiverConfig = $archiver;

        $this->component->archive($this->createMock(CategoryRepositoryInterface::class));
    }

    public function testReviveShouldRunArchiversRevive(): void
    {
        $archiver = $this->createMock(ArchiverInterface::class);
        $archiver->expects(self::once())->method('revive')->willReturn(PodiumResponse::success());
        $this->component->archiverConfig = $archiver;

        $this->component->revive($this->createMock(CategoryRepositoryInterface::class));
    }
}
