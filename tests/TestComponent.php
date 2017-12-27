<?php

namespace bizley\podium\api\tests;

use bizley\podium\api\repositories\RepoNotFoundException;
use yii\db\Query;

class TestComponent extends TestCase
{
    protected static $inputErrorData = [];
    protected static $inputSuccessData = [];
    protected static $updateSuccessData = [];
    protected static $selectQuery = [];
    protected static $insertCondition = [];
    protected static $updateCondition = [];
    protected static $addedRepo = [];
    protected static $updatedRepo = [];

    public function testAddingRepoErroneous()
    {
        $this->assertFalse($this->repo()->store(static::$inputErrorData));
        $this->assertNotEmpty($this->repo()->errors);
    }

    public function testAddingRepoSuccessful()
    {
        $this->assertTrue($this->repo()->store(static::$inputSuccessData));
        $this->assertEmpty($this->repo()->errors);

        $repo = (new Query())->select(static::$selectQuery)->from($this->tableName())->where(static::$insertCondition)->one(static::$db);
        $this->assertEquals(static::$addedRepo, $repo);
    }

    public function testAddingRepoDuplicate()
    {
        $this->assertTrue($this->repo()->store(static::$inputSuccessData));
        $this->assertFalse($this->repo(true)->store(static::$inputSuccessData));
        $this->assertNotEmpty($this->repo()->errors);
    }

    public function testRepoMissing()
    {
        $this->expectException(RepoNotFoundException::class);
        $this->assertFalse($this->repo()->fetch(-1));
    }

    public function testUpdatingRepoErroneous()
    {
        $repo = $this->repo();
        $this->assertTrue($repo->store(static::$inputSuccessData));
        $repoId = (new Query())->select($repo::primaryKey())->from($this->tableName())->where(static::$insertCondition)->scalar(static::$db);

        $this->assertEquals(0, $repo->fetch($repoId)->store(static::$inputErrorData));
        $this->assertNotEmpty($repo->errors);
    }

    public function testUpdatingRepoSuccessful()
    {
        $repo = $this->repo();
        $this->assertTrue($repo->store(static::$inputSuccessData));
        $repoId = (new Query())->select($repo::primaryKey())->from($this->tableName())->where(static::$insertCondition)->scalar(static::$db);

        $this->assertEquals(1, $this->repo()->fetch($repoId)->store(static::$updateSuccessData));
        $this->assertEmpty($this->repo()->errors);

        $repoTest = (new Query())->select(static::$selectQuery)->from($this->tableName())->where(static::$updateCondition)->one(static::$db);
        $this->assertEquals(static::$updatedRepo, $repoTest);
    }
}