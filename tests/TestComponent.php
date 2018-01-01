<?php

namespace bizley\podium\api\tests;

use bizley\podium\api\repositories\RepoNotFoundException;
use yii\db\Query;

class TestComponent extends TestCase
{
    public $data = [
        'inputErrorData' => [],
        'inputSuccessData' => [],
        'updateSuccessData' => [],
        'selectQuery' => [],
        'insertCondition' => [],
        'updateCondition' => [],
        'addedRepo' => [],
        'updatedRepo' => [],
    ];

    public function testAddingRepoErroneous()
    {
        $this->assertFalse($this->repo()->store($this->data['inputErrorData']));
        $this->assertNotEmpty($this->repo()->errors);
    }

    public function testAddingRepoSuccessful()
    {
        $this->assertTrue($this->repo()->store($this->data['inputSuccessData']));
        $this->assertEmpty($this->repo()->errors);

        $repo = (new Query())->select($this->data['selectQuery'])->from($this->tableName())->where($this->data['insertCondition'])->one(static::$db);
        $this->assertEquals($this->data['addedRepo'], $repo);
    }

    public function testAddingRepoDuplicate()
    {
        $this->assertTrue($this->repo()->store($this->data['inputSuccessData']));
        $this->assertFalse($this->repo(true)->store($this->data['inputSuccessData']));
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
        $repo->store($this->data['inputSuccessData']);
        $repoId = (new Query())->select($repo::primaryKey())->from($this->tableName())->where($this->data['insertCondition'])->scalar(static::$db);

        $this->assertEquals(0, $repo->fetch($repoId)->store($this->data['inputErrorData']));
        $this->assertNotEmpty($repo->errors);
    }

    public function testUpdatingRepoSuccessful()
    {
        $repo = $this->repo();
        $repo->store($this->data['inputSuccessData']);
        $repoId = (new Query())->select($repo::primaryKey())->from($this->tableName())->where($this->data['insertCondition'])->scalar(static::$db);

        $this->assertEquals(1, $this->repo()->fetch($repoId)->store($this->data['updateSuccessData']));
        $this->assertEmpty($this->repo()->errors);

        $repoTest = (new Query())->select($this->data['selectQuery'])->from($this->tableName())->where($this->data['updateCondition'])->one(static::$db);
        $this->assertEquals($this->data['updatedRepo'], $repoTest);
    }

    public function testDeletingRepoSuccessful()
    {
        $repo = $this->repo();
        $repo->store($this->data['inputSuccessData']);
        $repoId = (new Query())->select($repo::primaryKey())->from($this->tableName())->where($this->data['insertCondition'])->scalar(static::$db);

        $this->assertEquals(1, $this->repo()->fetch($repoId)->remove());
    }
}
