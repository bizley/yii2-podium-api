<?php

namespace bizley\podium\api\tests;

use bizley\podium\api\repositories\Member as MemberRepo;
use bizley\podium\api\repositories\RepoNotFoundException;
use yii\db\Query;

class MemberRepoTest extends TestCase
{
    /**
     * @param bool $clear
     * @return MemberRepo|bool
     */
    protected function repo($clear = false)
    {
        return $this->podium()->member->getRepo('member', $clear);
    }

    protected function tableName()
    {
        return MemberRepo::tableName();
    }

    public function testAddingRepoErroneous()
    {
        $this->assertFalse($this->repo(true)->store(['username' => null]));
        $this->assertNotEmpty($this->repo()->errors);
    }

    public function testAddingRepoSuccessful()
    {
        $this->assertTrue($this->repo(true)->store(['username' => 'test']));
        $this->assertEmpty($this->repo()->errors);

        $repo = (new Query())->select(['username', 'slug', 'status'])->from($this->tableName())->where(['username' => 'test'])->one(static::$db);
        $this->assertEquals([
            'username' => 'test',
            'slug' => 'test',
            'status' => '0',
        ], $repo);
    }

    public function testAddingRepoDuplicate()
    {
        $this->assertTrue($this->repo(true)->store(['username' => 'test2']));
        $this->assertFalse($this->repo(true)->store(['username' => 'test2']));
        $this->assertNotEmpty($this->repo()->errors);
    }

    public function testRepoMissing()
    {
        $this->expectException(RepoNotFoundException::class);
        $this->assertFalse($this->repo(true)->fetch(-1));
    }

    public function testUpdatingRepoErroneous()
    {
        $repo = $this->repo(true);
        $repo->store(['username' => 'test3']);
        $repoId = $repo->id;

        $this->assertEquals(0, $repo->fetch($repoId)->store(['username' => null]));
        $this->assertNotEmpty($repo->errors);

        $repoTest = (new Query())->select('username')->from($this->tableName())->where(['username' => 'test3'])->one(static::$db);
        $this->assertEquals(['username' => 'test3'], $repoTest);
    }

    public function testUpdatingRepoSuccessful()
    {
        $repo = $this->repo(true);
        $repo->store(['username' => 'test4']);
        $repoId = $repo->id;

        $this->assertEquals(1, $this->repo(true)->fetch($repoId)->store(['username' => 'testUpdated']));
        $this->assertEmpty($this->repo()->errors);

        $repoTest = (new Query())->select(['username', 'slug', 'status'])->from($this->tableName())->where(['username' => 'testUpdated'])->one(static::$db);
        $this->assertEquals([
            'username' => 'testUpdated',
            'slug' => 'testupdated',
            'status' => '0',
        ], $repoTest);
        $repoGone = (new Query())->select(['username', 'slug', 'status'])->from($this->tableName())->where(['username' => 'test4'])->one(static::$db);
        $this->assertEmpty($repoGone);
    }

    public function testCheckingRepoExists()
    {
        $repo = $this->repo(true);
        $repo->store(['username' => 'test5']);

        $this->assertTrue($repo->check(['id' => $repo->id]));
    }

    public function testCheckingRepoNonExists()
    {
        $this->assertFalse($this->repo(true)->check(['id' => -1]));
    }

    public function testDeletingRepoSuccessful()
    {
        $repo = $this->repo(true);
        $repo->store(['username' => 'test6']);
        $repoId = $repo->id;

        $this->assertEquals(1, $this->repo(true)->fetch($repoId)->remove());
    }
}
