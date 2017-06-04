<?php

namespace bizley\podium\api;

use yii\base\InvalidConfigException;
use yii\base\InvalidParamException;
use yii\db\BaseActiveRecord;
use yii\di\Instance;

/**
 * General Podium Component
 *
 * @property string|array|null $id
 * @property array $errors
 */
abstract class PodiumComponent extends \yii\base\Component
{
    /**
     * @var PodiumApi
     */
    public $podium;

    /**
     * @var array|string|object Repository configuration
     */
    public $repositoryConfig;

    /**
     * @var BaseActiveRecord
     */
    public $repository;


    /**
     * Returns single repository object based on its primary key.
     * @param mixed $id
     * @return mixed
     * @throws InvalidConfigException
     */
    public function get($id)
    {
        $repository = $this->prepare();
        return $repository::findOne($id);
    }

    /**
     * Prepares repository object.
     * @return object
     * @throws InvalidConfigException
     */
    public function prepare()
    {
        $this->repository = Instance::ensure($this->repositoryConfig, BaseActiveRecord::class);
        return $this->repository;
    }

    /**
     * Loads data to the repository.
     * @param array $data array of pairs attribute's name => attribute's value
     * @param null|BaseActiveRecord $repository
     * @return null|BaseActiveRecord
     * @throws InvalidParamException
     */
    protected function load($data, $repository = null)
    {
        if ($repository === null) {
            return null;
        }
        foreach ($data as $attribute => $value) {
            if ($repository->hasAttribute($attribute)) {
                $repository->setAttribute($attribute, $value);
            }
        }
        return $repository;
    }

    /**
     * Adds new repository data.
     * @param array $data array of pairs attribute's name => attribute's value
     * @param bool $runValidation whether to perform repository validation before saving the record. Defaults to `true`.
     * If the validation fails, the record will not be saved to the database and this method will return `false`.
     * @param null|array $attributes list of attributes that need to be saved. Defaults to `null`, meaning all
     * attributes that are loaded will be saved.
     * @return bool
     * @throws InvalidConfigException
     * @throws InvalidParamException
     */
    public function add($data, $runValidation = true, $attributes = null)
    {
        $loadedRepo = $this->load($data, $this->prepare());
        if ($loadedRepo === null) {
            return false;
        }
        return $loadedRepo->insert($runValidation, $attributes);
    }

    /**
     * Updates repository data of given primary key.
     * @param mixed $id
     * @param array $data array of pairs attribute's name => attribute's value
     * @param bool $runValidation whether to perform repository validation before saving the record. Defaults to `true`.
     * If the validation fails, the record will not be saved to the database and this method will return `false`.
     * @param null|array $attributes list of attributes that need to be saved. Defaults to `null`, meaning all
     * attributes that are loaded will be saved.
     * @return bool|int
     * @throws InvalidConfigException
     * @throws InvalidParamException
     * @throws \yii\db\Exception
     * @throws \yii\db\StaleObjectException
     */
    public function update($id, $data, $runValidation = true, $attributes = null)
    {
        $loadedRepo = $this->load($data, $this->get($id));
        if ($loadedRepo === null) {
            return false;
        }
        return $loadedRepo->update($runValidation, $attributes);
    }

    /**
     * Deletes repository data of given primary key.
     * @param mixed $id
     * @return int|bool
     * @throws InvalidConfigException
     */
    public function delete($id)
    {
        $loadedRepo = $this->get($id);
        if ($loadedRepo === null) {
            return false;
        }
        return $loadedRepo->delete();
    }

    /**
     * Returns repository primary key value.
     * @return mixed
     */
    public function getId()
    {
        return $this->repository->getPrimaryKey();
    }

    /**
     * Returns the errors for all repository attributes or a single attribute when getErrors method is available for
     * repository or empty array otherwise.
     * @param null|string $attribute attribute name. Use null to retrieve errors for all attributes.
     * @return array
     */
    public function getErrors($attribute = null)
    {
        return $this->repository->getErrors($attribute);
    }
}
