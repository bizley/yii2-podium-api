<?php

declare(strict_types=1);

namespace bizley\podium\tests\unit\forum;

use bizley\podium\api\ars\ForumActiveRecord;
use bizley\podium\api\components\Forum;
use bizley\podium\api\components\PodiumResponse;
use bizley\podium\api\interfaces\ActiveRecordRepositoryInterface;
use bizley\podium\api\interfaces\ArchiverInterface;
use bizley\podium\api\interfaces\CategorisedBuilderInterface;
use bizley\podium\api\interfaces\CategoryRepositoryInterface;
use bizley\podium\api\interfaces\ForumRepositoryInterface;
use bizley\podium\api\interfaces\MemberRepositoryInterface;
use bizley\podium\api\interfaces\MoverInterface;
use bizley\podium\api\interfaces\RemoverInterface;
use bizley\podium\api\interfaces\SorterInterface;
use PHPUnit\Framework\TestCase;
use yii\base\InvalidConfigException;

class ForumComponentTest extends TestCase
{
    private Forum $component;

    protected function setUp(): void
    {
        $this->component = new Forum();
    }

    public function testGetByIdShouldThrowExceptionWhenRepositoryIsMisconfigured(): void
    {
        $this->expectException(InvalidConfigException::class);

        $this->component->repositoryConfig = '';

        $this->component->getById(1);
    }

    public function testGetByIdShouldReturnNullWhenModelDoesntExist(): void
    {
        $forumRepository = $this->createMock(ActiveRecordRepositoryInterface::class);
        $forumRepository->method('fetchOne')->willReturn(false);
        $forumRepository->method('getModel')->willReturn(new ForumActiveRecord());

        $this->component->repositoryConfig = $forumRepository;

        self::assertNull($this->component->getById(1));
    }

    public function testGetByIdShouldReturnForumARWhenModelExists(): void
    {
        $forumRepository = $this->createMock(ActiveRecordRepositoryInterface::class);
        $forumRepository->method('fetchOne')->willReturn(true);
        $forumRepository->method('getModel')->willReturn(new ForumActiveRecord());

        $this->component->repositoryConfig = $forumRepository;

        self::assertInstanceOf(ForumActiveRecord::class, $this->component->getById(1));
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
        $builder = $this->createMock(CategorisedBuilderInterface::class);
        $builder->expects(self::once())->method('create')->willReturn(PodiumResponse::success());
        $this->component->builderConfig = $builder;

        $this->component->create(
            $this->createMock(MemberRepositoryInterface::class),
            $this->createMock(CategoryRepositoryInterface::class)
        );
    }

    public function testEditShouldRunBuildersEdit(): void
    {
        $builder = $this->createMock(CategorisedBuilderInterface::class);
        $builder->expects(self::once())->method('edit')->willReturn(PodiumResponse::success());
        $this->component->builderConfig = $builder;

        $this->component->edit($this->createMock(ForumRepositoryInterface::class));
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

        $this->component->remove($this->createMock(ForumRepositoryInterface::class));
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

        $category = $this->createMock(ForumRepositoryInterface::class);
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

        $this->component->archive($this->createMock(ForumRepositoryInterface::class));
    }

    public function testReviveShouldRunArchiversRevive(): void
    {
        $archiver = $this->createMock(ArchiverInterface::class);
        $archiver->expects(self::once())->method('revive')->willReturn(PodiumResponse::success());
        $this->component->archiverConfig = $archiver;

        $this->component->revive($this->createMock(ForumRepositoryInterface::class));
    }

    public function testGetMoverShouldThrowExceptionWhenMoverIsMisconfigured(): void
    {
        $this->expectException(InvalidConfigException::class);

        $this->component->moverConfig = '';

        $this->component->getMover();
    }

    public function testMoveShouldRunMoversMove(): void
    {
        $mover = $this->createMock(MoverInterface::class);
        $mover->expects(self::once())->method('move')->willReturn(PodiumResponse::success());
        $this->component->moverConfig = $mover;

        $this->component->move(
            $this->createMock(ForumRepositoryInterface::class),
            $this->createMock(CategoryRepositoryInterface::class)
        );
    }
}
