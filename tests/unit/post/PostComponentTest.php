<?php

declare(strict_types=1);

namespace bizley\podium\tests\unit\post;

use bizley\podium\api\ars\PostActiveRecord;
use bizley\podium\api\components\PodiumResponse;
use bizley\podium\api\components\Post;
use bizley\podium\api\interfaces\ActiveRecordRepositoryInterface;
use bizley\podium\api\interfaces\ArchiverInterface;
use bizley\podium\api\interfaces\CategorisedBuilderInterface;
use bizley\podium\api\interfaces\LikerInterface;
use bizley\podium\api\interfaces\MemberRepositoryInterface;
use bizley\podium\api\interfaces\MoverInterface;
use bizley\podium\api\interfaces\PinnerInterface;
use bizley\podium\api\interfaces\PostRepositoryInterface;
use bizley\podium\api\interfaces\RemoverInterface;
use bizley\podium\api\interfaces\ThreadRepositoryInterface;
use PHPUnit\Framework\TestCase;
use yii\base\InvalidConfigException;

class PostComponentTest extends TestCase
{
    private Post $component;

    protected function setUp(): void
    {
        $this->component = new Post();
    }

    public function testGetByIdShouldThrowExceptionWhenRepositoryIsMisconfigured(): void
    {
        $this->expectException(InvalidConfigException::class);

        $this->component->repositoryConfig = '';

        $this->component->getById(1);
    }

    public function testGetByIdShouldReturnNullWhenModelDoesntExist(): void
    {
        $postRepository = $this->createMock(ActiveRecordRepositoryInterface::class);
        $postRepository->method('fetchOne')->willReturn(false);
        $postRepository->method('getModel')->willReturn(new PostActiveRecord());

        $this->component->repositoryConfig = $postRepository;

        self::assertNull($this->component->getById(1));
    }

    public function testGetByIdShouldReturnPostARWhenModelExists(): void
    {
        $postRepository = $this->createMock(ActiveRecordRepositoryInterface::class);
        $postRepository->method('fetchOne')->willReturn(true);
        $postRepository->method('getModel')->willReturn(new PostActiveRecord());

        $this->component->repositoryConfig = $postRepository;

        self::assertInstanceOf(PostActiveRecord::class, $this->component->getById(1));
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
            $this->createMock(ThreadRepositoryInterface::class)
        );
    }

    public function testEditShouldRunBuildersEdit(): void
    {
        $builder = $this->createMock(CategorisedBuilderInterface::class);
        $builder->expects(self::once())->method('edit')->willReturn(PodiumResponse::success());
        $this->component->builderConfig = $builder;

        $this->component->edit($this->createMock(PostRepositoryInterface::class));
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

        $this->component->remove($this->createMock(PostRepositoryInterface::class));
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

        $this->component->archive($this->createMock(PostRepositoryInterface::class));
    }

    public function testReviveShouldRunArchiversRevive(): void
    {
        $archiver = $this->createMock(ArchiverInterface::class);
        $archiver->expects(self::once())->method('revive')->willReturn(PodiumResponse::success());
        $this->component->archiverConfig = $archiver;

        $this->component->revive($this->createMock(PostRepositoryInterface::class));
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
            $this->createMock(PostRepositoryInterface::class),
            $this->createMock(ThreadRepositoryInterface::class)
        );
    }

    public function testGetPinnerShouldThrowExceptionWhenPinnerIsMisconfigured(): void
    {
        $this->expectException(InvalidConfigException::class);

        $this->component->pinnerConfig = '';

        $this->component->getPinner();
    }

    public function testPinShouldRunPinnersPin(): void
    {
        $pinner = $this->createMock(PinnerInterface::class);
        $pinner->expects(self::once())->method('pin')->willReturn(PodiumResponse::success());
        $this->component->pinnerConfig = $pinner;

        $this->component->pin($this->createMock(PostRepositoryInterface::class));
    }

    public function testUnpinShouldRunPinnersUnpin(): void
    {
        $pinner = $this->createMock(PinnerInterface::class);
        $pinner->expects(self::once())->method('unpin')->willReturn(PodiumResponse::success());
        $this->component->pinnerConfig = $pinner;

        $this->component->unpin($this->createMock(PostRepositoryInterface::class));
    }

    public function testGetLikerShouldThrowExceptionWhenLikerIsMisconfigured(): void
    {
        $this->expectException(InvalidConfigException::class);

        $this->component->likerConfig = '';

        $this->component->getLiker();
    }

    public function testThumbUpShouldRunLikersThumbUp(): void
    {
        $liker = $this->createMock(LikerInterface::class);
        $liker->expects(self::once())->method('thumbUp')->willReturn(PodiumResponse::success());
        $this->component->likerConfig = $liker;

        $this->component->thumbUp(
            $this->createMock(PostRepositoryInterface::class),
            $this->createMock(MemberRepositoryInterface::class)
        );
    }

    public function testThumbDownShouldRunLikersThumbDown(): void
    {
        $liker = $this->createMock(LikerInterface::class);
        $liker->expects(self::once())->method('thumbDown')->willReturn(PodiumResponse::success());
        $this->component->likerConfig = $liker;

        $this->component->thumbDown(
            $this->createMock(PostRepositoryInterface::class),
            $this->createMock(MemberRepositoryInterface::class)
        );
    }

    public function testThumbResetShouldRunLikersThumbReset(): void
    {
        $liker = $this->createMock(LikerInterface::class);
        $liker->expects(self::once())->method('thumbReset')->willReturn(PodiumResponse::success());
        $this->component->likerConfig = $liker;

        $this->component->thumbReset(
            $this->createMock(PostRepositoryInterface::class),
            $this->createMock(MemberRepositoryInterface::class)
        );
    }
}
