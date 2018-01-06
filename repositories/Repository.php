<?php

namespace bizley\podium\api\repositories;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * General Podium Repository
 */
abstract class Repository extends ActiveRecord implements RepositoryInterface
{
    /**
     * @inheritdoc
     */
    public function loadData($data)
    {
        return $this->load($data, '');
    }

    /**
     * @inheritdoc
     */
    public function fetch($primaryKey)
    {
        $repoClass = static::class;
        /* @var $repository static */
        $repository = $repoClass::find()->where($primaryKey)->one();
        if (null === $repository) {
            throw new RepoNotFoundException("$repoClass data has not been found");
        }
        $this->setAttributes($repository->getAttributes(), false);
        $this->setIsNewRecord(false);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function check($primaryKey)
    {
        $repoClass = static::class;
        /* @var $repository static */
        return $repoClass::find()->where($primaryKey)->exists();
    }

    /**
     * @inheritdoc
     */
    public function store($data, $runValidation = true, $attributeNames = null)
    {
        if (!$this->loadData($data)) {
            return false;
        }
        return $this->save($runValidation, $attributeNames);
    }

    /**
     * @inheritdoc
     */
    public function remove()
    {
        return $this->delete();
    }

    public function browse($filter)
    {
        $repoClass = static::class;
        /* @var $query ActiveQuery */
        $query = $repoClass::find();

    }
}
