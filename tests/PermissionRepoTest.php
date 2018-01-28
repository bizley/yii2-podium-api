<?php

namespace bizley\podium\api\tests;

use bizley\podium\api\dictionaries\Permission;
use bizley\podium\api\repositories\Permission as PermissionRepo;
use bizley\podium\api\repositories\Member as MemberRepo;
use bizley\podium\api\repositories\RepoNotFoundException;
use yii\db\Query;

class PermissionRepoTest extends TestCase
{
    /**
     * @param bool $clear
     * @return PermissionRepo|bool
     */
    protected function permRepo($clear = false)
    {
        return $this->podium()->access->getRepo('permission', $clear);
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
        return PermissionRepo::tableName();
    }

    public function testAddingRepoErroneous()
    {
        $this->assertFalse($this->permRepo(true)->store(['member_id' => null]));
        $this->assertNotEmpty($this->permRepo()->errors);
    }

    public function testAddingRepoSuccessful()
    {
        $this->memRepo(true)->store(['username' => 'member']);
        $memberId = $this->memRepo()->id;

        $this->assertTrue($this->permRepo(true)->store(['member_id' => $memberId, 'permission' => Permission::MEMBER_ACQUAINTANCE]));
        $this->assertEmpty($this->permRepo()->errors);

        $repo = (new Query())->select(['member_id', 'permission'])->from($this->tableName())->where(['member_id' => $memberId, 'permission' => Permission::MEMBER_ACQUAINTANCE])->one(static::$db);
        $this->assertEquals([
            'member_id' => $memberId,
            'permission' => Permission::MEMBER_ACQUAINTANCE,
        ], $repo);
    }

    public function testAddingRepoDuplicate()
    {
        $this->memRepo(true)->store(['username' => 'member2']);
        $memberId = $this->memRepo()->id;

        $this->assertTrue($this->permRepo(true)->store(['member_id' => $memberId, 'permission' => Permission::MEMBER_ACQUAINTANCE]));
        $this->assertFalse($this->permRepo(true)->store(['member_id' => $memberId, 'permission' => Permission::MEMBER_ACQUAINTANCE]));
        $this->assertNotEmpty($this->permRepo()->errors);
    }

    public function testRepoMissing()
    {
        $this->expectException(RepoNotFoundException::class);
        $this->assertFalse($this->permRepo(true)->fetch(-1));
    }

    public function testUpdatingRepoErroneous()
    {
        $this->memRepo(true)->store(['username' => 'member3']);
        $memberId = $this->memRepo()->id;
        $this->permRepo(true)->store(['member_id' => $memberId, 'permission' => Permission::MEMBER_ACQUAINTANCE]);

        $this->assertEquals(0, $this->permRepo(true)->fetch(['member_id' => $memberId, 'permission' => Permission::MEMBER_ACQUAINTANCE])->store(['member_id' => null]));
        $this->assertNotEmpty($this->permRepo()->errors);

        $repoTest = (new Query())->select('member_id')->from($this->tableName())->where(['member_id' => $memberId, 'permission' => Permission::MEMBER_ACQUAINTANCE])->one(static::$db);
        $this->assertEquals(['member_id' => $memberId], $repoTest);
    }

    public function testUpdatingRepoSuccessful()
    {
        $this->memRepo(true)->store(['username' => 'member4']);
        $member1Id = $this->memRepo()->id;
        $this->memRepo(true)->store(['username' => 'member5']);
        $member2Id = $this->memRepo()->id;
        $this->permRepo(true)->store(['member_id' => $member1Id, 'permission' => Permission::MEMBER_ACQUAINTANCE]);

        $this->assertEquals(1, $this->permRepo(true)->fetch(['member_id' => $member1Id, 'permission' => Permission::MEMBER_ACQUAINTANCE])->store(['member_id' => $member2Id]));
        $this->assertEmpty($this->permRepo()->errors);

        $repoTest = (new Query())->select(['member_id', 'permission'])->from($this->tableName())->where(['member_id' => $member2Id, 'permission' => Permission::MEMBER_ACQUAINTANCE])->one(static::$db);
        $this->assertEquals([
            'member_id' => $member2Id,
            'permission' => Permission::MEMBER_ACQUAINTANCE,
        ], $repoTest);
        $repoGone = (new Query())->select(['member_id', 'permission'])->from($this->tableName())->where(['member_id' => $member1Id, 'permission' => Permission::MEMBER_ACQUAINTANCE])->one(static::$db);
        $this->assertEmpty($repoGone);
    }

    public function testCheckingRepoExists()
    {
        $this->memRepo(true)->store(['username' => 'member6']);
        $memberId = $this->memRepo()->id;
        $this->permRepo(true)->store(['member_id' => $memberId, 'permission' => Permission::MEMBER_ACQUAINTANCE]);

        $this->assertTrue($this->permRepo()->check(['member_id' => $memberId, 'permission' => Permission::MEMBER_ACQUAINTANCE]));
    }

    public function testCheckingRepoNonExists()
    {
        $this->assertFalse($this->permRepo(true)->check(['member_id' => -1]));
    }

    public function testDeletingRepoSuccessful()
    {
        $this->memRepo(true)->store(['username' => 'member7']);
        $memberId = $this->memRepo()->id;
        $this->permRepo(true)->store(['member_id' => $memberId, 'permission' => Permission::MEMBER_ACQUAINTANCE]);

        $this->assertEquals(1, $this->permRepo(true)->fetch(['member_id' => $memberId, 'permission' => Permission::MEMBER_ACQUAINTANCE])->remove());
    }
}
