<?php

namespace bizley\podium\api\repositories;

use yii\db\ActiveRecordInterface;

/**
 * Podium Repository Interface
 */
interface RepositoryInterface extends ActiveRecordInterface
{
    /**
     * Loads data into the repository.
     * @param array $data array of pairs attribute's name => attribute's value
     * @return bool whether data has been loaded
     */
    public function loadData($data);

    /**
     * Fetches single repository data based on its primary key value.
     * @param mixed $primaryKey
     * @return static
     * @throws RepoNotFoundException if repository data has not been found
     */
    public function fetch($primaryKey);

    /**
     * Save repository data.
     * @param array $data array of pairs attribute's name => attribute's value
     * @param bool $runValidation whether to perform repository validation before saving the record. Defaults to `true`.
     * If the validation fails, the record will not be saved to the database and this method will return `false`.
     * @param null|array $attributeNames list of attribute names that need to be saved. Defaults to null,
     * meaning all attributes that are loaded from DB will be saved.
     * @return bool whether the attributes are valid and the data is inserted successfully
     * @throws \Exception
     */
    public function store($data, $runValidation = true, $attributeNames = null);
}
