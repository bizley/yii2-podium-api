<?php

namespace bizley\podium\api\tests;

use bizley\podium\api\dictionaries\Acquaintance;
use bizley\podium\api\repositories\Acquaintance as AcquaintanceRepo;
use bizley\podium\api\repositories\Member as MemberRepo;
use bizley\podium\api\repositories\RepoNotFoundException;
use yii\db\Query;

class AcquaintanceRepoTest extends TestCase
{
    /**
     * @param bool $clear
     * @return AcquaintanceRepo|bool
     */
    protected function acqRepo($clear = false)
    {
        return $this->podium()->member->getRepo('acquaintance', $clear);
    }

    /**
     * @param bool $clear
     * @return MemberRepo|bool
     */
    protected function memRepo($clear = false)
    {
        return $this->podium()->member->getRepo('member', $clear);
    }

    protected function tableName()
    {
        return AcquaintanceRepo::tableName();
    }

    public function testAddingRepoErroneous()
    {
        $this->assertFalse($this->acqRepo(true)->store(['member_id' => null]));
        $this->assertNotEmpty($this->acqRepo()->errors);
    }

    public function testAddingRepoSuccessful()
    {
        $this->memRepo(true)->store(['username' => 'member']);
        $memberId = $this->memRepo()->id;
        $this->memRepo(true)->store(['username' => 'target']);
        $targetId = $this->memRepo()->id;

        $this->assertTrue($this->acqRepo(true)->store(['member_id' => $memberId, 'target_id' => $targetId, 'type' => Acquaintance::FRIEND]));
        $this->assertEmpty($this->acqRepo()->errors);
        $acqId = $this->acqRepo()->id;

        $repo = (new Query())->select(['member_id', 'target_id', 'type'])->from($this->tableName())->where(['id' => $acqId])->one(static::$db);
        $this->assertEquals([
            'member_id' => $memberId,
            'target_id' => $targetId,
            'type' => Acquaintance::FRIEND,
        ], $repo);
    }

    public function testAddingRepoDuplicate()
    {
        $this->memRepo(true)->store(['username' => 'member2']);
        $memberId = $this->memRepo()->id;
        $this->memRepo(true)->store(['username' => 'target2']);
        $targetId = $this->memRepo()->id;

        $this->assertTrue($this->acqRepo(true)->store(['member_id' => $memberId, 'target_id' => $targetId, 'type' => Acquaintance::FRIEND]));
        $this->assertFalse($this->acqRepo(true)->store(['member_id' => $memberId, 'target_id' => $targetId, 'type' => Acquaintance::FRIEND]));
        $this->assertNotEmpty($this->acqRepo()->errors);
    }

    public function testRepoMissing()
    {
        $this->expectException(RepoNotFoundException::class);
        $this->assertFalse($this->acqRepo(true)->fetch(-1));
    }

    public function testUpdatingRepoErroneous()
    {
        $this->memRepo(true)->store(['username' => 'member3']);
        $memberId = $this->memRepo()->id;
        $this->memRepo(true)->store(['username' => 'target3']);
        $targetId = $this->memRepo()->id;
        $this->acqRepo(true)->store(['member_id' => $memberId, 'target_id' => $targetId, 'type' => Acquaintance::FRIEND]);
        $repoId = $this->acqRepo()->id;

        $this->assertEquals(0, $this->acqRepo(true)->fetch($repoId)->store(['member_id' => null]));
        $this->assertNotEmpty($this->acqRepo()->errors);

        $repoTest = (new Query())->select('member_id')->from($this->tableName())->where(['id' => $repoId])->one(static::$db);
        $this->assertEquals(['member_id' => $memberId], $repoTest);
    }

    public function testUpdatingRepoSuccessful()
    {
        $this->memRepo(true)->store(['username' => 'member4']);
        $memberId = $this->memRepo()->id;
        $this->memRepo(true)->store(['username' => 'target4']);
        $targetId = $this->memRepo()->id;
        $this->acqRepo(true)->store(['member_id' => $memberId, 'target_id' => $targetId, 'type' => Acquaintance::FRIEND]);
        $repoId = $this->acqRepo()->id;

        $this->assertEquals(1, $this->acqRepo(true)->fetch($repoId)->store(['type' => Acquaintance::IGNORE]));
        $this->assertEmpty($this->acqRepo()->errors);

        $repoTest = (new Query())->select(['member_id', 'target_id', 'type'])->from($this->tableName())->where(['id' => $repoId])->one(static::$db);
        $this->assertEquals([
            'member_id' => $memberId,
            'target_id' => $targetId,
            'type' => Acquaintance::IGNORE,
        ], $repoTest);
        $repoGone = (new Query())->select(['member_id', 'target_id', 'type'])->from($this->tableName())->where(['member_id' => $memberId, 'target_id' => $targetId, 'type' => Acquaintance::FRIEND])->one(static::$db);
        $this->assertEmpty($repoGone);
    }

    public function testCheckingRepoExists()
    {
        $this->memRepo(true)->store(['username' => 'member5']);
        $memberId = $this->memRepo()->id;
        $this->memRepo(true)->store(['username' => 'target5']);
        $targetId = $this->memRepo()->id;
        $this->acqRepo(true)->store(['member_id' => $memberId, 'target_id' => $targetId, 'type' => Acquaintance::FRIEND]);
        $repoId = $this->acqRepo()->id;

        $this->assertTrue($this->acqRepo()->check(['id' => $repoId]));
    }

    public function testCheckingRepoNonExists()
    {
        $repo = $this->acqRepo(true);
        $this->assertFalse($this->acqRepo()->check(['id' => -1]));
    }

    public function testDeletingRepoSuccessful()
    {
        $this->memRepo(true)->store(['username' => 'member6']);
        $memberId = $this->memRepo()->id;
        $this->memRepo(true)->store(['username' => 'target6']);
        $targetId = $this->memRepo()->id;
        $this->acqRepo(true)->store(['member_id' => $memberId, 'target_id' => $targetId, 'type' => Acquaintance::FRIEND]);
        $repoId = $this->acqRepo()->id;

        $this->assertEquals(1, $this->acqRepo(true)->fetch($repoId)->remove());
    }
}
