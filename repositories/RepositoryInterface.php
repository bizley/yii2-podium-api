<?php

namespace bizley\podium\api\repositories;

use yii\db\ActiveRecordInterface;
use yii\db\StaleObjectException;

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

    /**
     * Deletes the repository data.
     * @return int|false the number of rows deleted, or `false` if the deletion is unsuccessful for some reason.
     * Note that it is possible the number of rows deleted is 0, even though the deletion execution is successful.
     * @throws StaleObjectException if [[optimisticLock|optimistic locking]] is enabled and the data
     * being deleted is outdated.
     * @throws \Exception|\Throwable in case delete failed.
     */
    public function remove();
}
