<?php

namespace bizley\podium\api\tests;

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
        $this->assertFalse($this->api()->add(static::$inputErrorData));
        $this->assertNotEmpty($this->api()->errors);
    }

    public function testAddingRepoSuccessful()
    {
        $this->assertTrue($this->api()->add(static::$inputSuccessData));
        $this->assertEmpty($this->api()->errors);

        $repo = (new Query())->select(static::$selectQuery)->from($this->tableName())->where(static::$insertCondition)->one(static::$db);
        $this->assertEquals(static::$addedRepo, $repo);
    }

    public function testAddingRepoDuplicate()
    {
        $this->assertTrue($this->api()->add(static::$inputSuccessData));
        $this->assertFalse($this->api()->add(static::$inputSuccessData));
        $this->assertNotEmpty($this->api()->errors);
    }

    public function testUpdatingRepoMissing()
    {
        $this->assertFalse($this->api()->update(-1, []));
    }

    public function testUpdatingRepoErroneous()
    {
        $this->assertTrue($this->api()->add(static::$inputSuccessData));
        $repoClass = $this->api()->repository;
        $repoId = (new Query())->select($repoClass::primaryKey())->from($this->tableName())->where(static::$insertCondition)->scalar(static::$db);

        $this->assertEquals(0, $this->api()->update($repoId, static::$inputErrorData));
        $this->assertNotEmpty($this->api()->errors);
    }

    public function testUpdatingRepoSuccessful()
    {
        $this->assertTrue($this->api()->add(static::$inputSuccessData));
        $repoClass = $this->api()->repository;
        $repoId = (new Query())->select($repoClass::primaryKey())->from($this->tableName())->where(static::$insertCondition)->scalar(static::$db);

        $this->assertEquals(1, $this->api()->update($repoId, static::$updateSuccessData));
        $this->assertEmpty($this->api()->errors);

        $repo = (new Query())->select(static::$selectQuery)->from($this->tableName())->where(static::$updateCondition)->one(static::$db);
        $this->assertEquals(static::$updatedRepo, $repo);
    }
}