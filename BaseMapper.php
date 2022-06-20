<?php

/**
 * Copyright 2021 Jeremy Presutti <Jeremy@Presutti.us>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

declare(strict_types=1);

namespace Feast;

use Exception;
use Feast\Collection\Set;
use Feast\Database\Query;
use Feast\Exception\ServerFailureException;
use Feast\Interfaces\DatabaseDetailsInterface;
use Feast\Interfaces\DatabaseFactoryInterface;
use Feast\Interfaces\DatabaseInterface;
use PDO;
use stdClass;

/**
 * @psalm-consistent-constructor
 */
abstract class BaseMapper
{
    /** @var string TABLE_NAME */
    public const TABLE_NAME = null;
    protected const PRIMARY_KEY = null;
    protected const OBJECT_NAME = null;
    /** @var string|null SEQUENCE_NAME */
    protected const SEQUENCE_NAME = null;
    public const CONNECTION = 'default';
    public const NOT_NULL = 'not_null';
    protected DatabaseInterface $connection;

    /**
     * @throws ServiceContainer\NotFoundException
     * @throws ServerFailureException
     */
    public function __construct()
    {
        $this->connection = di(DatabaseFactoryInterface::class)->getConnection((string)static::CONNECTION);
    }

    /**
     * Map an array to a Model.
     *
     * @param array $row
     * @param array $dataTypes
     * @return BaseModel
     * @throws ServerFailureException
     */
    protected function map(array $row, array $dataTypes): BaseModel
    {
        $class = (string)static::OBJECT_NAME;
        /** @var BaseModel $return */
        $return = new $class();
        /**
         * @var string $key
         * @var string|null|int $val
         */
        foreach ($row as $key => $val) {
            if (property_exists($return, $key)) {
                if ($val === null) {
                    $return->$key = null;
                    continue;
                }
                $return->$key = match ($dataTypes[$key]) {
                    Date::class => Date::createFromString((string)$val),
                    stdClass::class => (object)json_decode(
                        (string)$val
                    ),
                    'int' => (int)$val,
                    'bool' => $this->getBoolValue($val),
                    default => utf8_encode((string)$val)
                };
            }
        }

        $return->makeOriginalModel();

        return $return;
    }

    protected function getBoolValue(string|int|bool $value): ?bool
    {
        return ($value === 'false') ? false : (bool)$value;
    }

    protected function getQueryBase(): Query
    {
        return $this->connection->select((string)static::TABLE_NAME);
    }

    /**
     * Check if table exists.
     *
     * @param string|null $table
     * @return bool
     * @throws Exception
     */
    public function tableExists(string $table = null): bool
    {
        $table = $table ?? (string)static::TABLE_NAME;

        return $this->connection->tableExists($table);
    }

    /**
     * Find model by Primary Key.
     *
     * @param int|string $value
     * @param bool $validate
     * @return BaseModel|null
     * @throws ServerFailureException
     * @throws ServiceContainer\NotFoundException
     */
    public function findByPrimaryKey(int|string $value, bool $validate = false): ?BaseModel
    {
        return $this->findOneByFields(
            [
                $this->getEscapedFieldName((string)static::PRIMARY_KEY) => $value
            ]
        );
    }

    /**
     * Find one record by field.
     *
     * @param string $field
     * @param string|int $value
     * @return BaseModel|null
     * @throws ServerFailureException
     * @throws ServiceContainer\NotFoundException
     */
    public function findOneByField(string $field, string|int $value): ?BaseModel
    {
        return $this->findOneByFields(
            [
                $field => $value
            ]
        );
    }

    /**
     * Find all records by field.
     *
     * @param string $field
     * @param mixed $value
     * @return Set
     * @throws ServerFailureException
     * @throws ServiceContainer\NotFoundException
     */
    public function findAllByField(string $field, mixed $value): Set
    {
        return $this->findAllByFields(
            [
                $field => $value
            ]
        );
    }

    protected function prepFindByFields(array $fields): Query
    {
        $select = $this->getQueryBase();
        /**
         * @var string $key
         * @var string|null|int|float $val
         */
        foreach ($fields as $key => $val) {
            if ($val !== null && $val != self::NOT_NULL) {
                $select->where($key . ' = ?', $val);
            } elseif ($val == self::NOT_NULL) {
                $select->where($key . ' IS NOT NULL');
            } else {
                $select->where($key . ' IS NULL');
            }
        }

        return $select;
    }

    /**
     * Find all records by key => value array of fields.
     *
     * @param array $fields
     * @return Set
     * @throws ServerFailureException|ServiceContainer\NotFoundException
     */
    public function findAllByFields(array $fields): Set
    {
        $select = $this->prepFindByFields($fields);

        return $this->fetchAll($select);
    }

    /**
     * Find one records by key => value array of fields.
     *
     * @param array $fields
     * @return ?BaseModel
     * @throws ServerFailureException
     * @throws ServiceContainer\NotFoundException
     */
    public function findOneByFields(array $fields): ?BaseModel
    {
        $select = $this->prepFindByFields($fields);

        return $this->fetchOne($select);
    }

    /**
     * Fetch one record. Optionally, pass in query object to filter on.
     *
     * @param Query|null $select
     * @return BaseModel|null
     * @throws ServerFailureException|ServiceContainer\NotFoundException
     */
    public function fetchOne(Query $select = null): ?BaseModel
    {
        $select = $select ?? $this->getQueryBase();

        $select->limit(1);
        /** @var BaseModel|null */
        return $this->fetchAll($select)->first();
    }

    /**
     * Fetch all record. Optionally, pass in query object to filter on.
     *
     * @param Query|null $select
     * @return Set
     * @throws ServerFailureException|ServiceContainer\NotFoundException|Exception
     */
    public function fetchAll(Query $select = null): Set
    {
        $rows = [];

        $select = $select ?? $this->getQueryBase();
        $statement = $select->execute();
        $dataTypes = di(DatabaseDetailsInterface::class)->getDataTypesForTable(
            (string)static::TABLE_NAME,
            (string)static::CONNECTION
        );
        // map each row to an object.
        while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            $rows[] = $this->map($row, $dataTypes);
        }

        return new Set((string)static::OBJECT_NAME, $rows, preValidated: true);
    }

    public function getEscapedFieldName(string $field): string
    {
        return $this->connection->getEscapedIdentifier($field);
    }

    /**
     * Save model to database.
     *
     * @param BaseModel $record
     * @param bool $forceUpdate
     * @throws Exception
     */
    public function save(BaseModel $record, bool $forceUpdate = false): void
    {
        $recordArray = $this->getFieldsForSave($record);
        if (count($recordArray) == 0) {
            return;
        }
        /** @var string $primaryKey */
        $primaryKey = static::PRIMARY_KEY;
        /** @var string|int|null $recordPrimaryKey */
        $recordPrimaryKey = $record->{$primaryKey} ?? null;
        if (!empty($recordPrimaryKey) && (!empty($record->getOriginalModel()) || $forceUpdate)) {
            unset($recordArray[$primaryKey]);
            $update = $this->connection->update((string)static::TABLE_NAME, $recordArray)->where(
                $this->getEscapedFieldName($primaryKey) . ' = ?',
                $recordPrimaryKey
            );
            $update->execute();
            $this->onSave($record, false);
        } else {
            $insert = $this->connection->insert((string)static::TABLE_NAME, $recordArray);
            $insert->execute();
            $lastInsert = $this->connection->lastInsertId((string)static::SEQUENCE_NAME);
            if (empty($record->{$primaryKey})) {
                $recordPrimaryKey = ctype_digit($lastInsert) && $lastInsert !== '0' ? (int)$lastInsert : $lastInsert;
                $record->{$primaryKey} = $recordPrimaryKey;
            }
            $this->onSave($record);
        }
        $record->makeOriginalModel();
    }

    /**
     * @param BaseModel $record
     * @return array
     */
    private function getFieldsForSave(BaseModel $record): array
    {
        $originalObject = $record->getOriginalModel();

        $fields = get_object_vars($record);
        if ($originalObject === null) {
            return $this->buildFieldDataForSave(null, $fields);
        }

        $original = get_object_vars($originalObject);

        unset($original[chr(0) . '*' . chr(0) . 'originalModel']);
        if ($original === $fields) {
            return [];
        }
        return $this->buildFieldDataForSave($originalObject, $fields);
    }

    protected function buildFieldDataForSave(BaseModel|null $originalObject, array $fields): array
    {
        $return = [];
        /**
         * @var string $field
         * @var int|string|Date|null|stdClass|array $val
         */
        foreach ($fields as $field => $val) {
            if ($originalObject !== null && $originalObject->$field === $val) {
                continue;
            }
            if ($val instanceof Date) {
                $val = $val->getFormattedDate();
            }
            if ($val instanceof stdClass || is_array($val)) {
                $val = json_encode($val);
            }
            $return[$this->getEscapedFieldName($field)] = $val;
        }
        return $return;
    }

    /**
     * Delete database records by field.
     *
     * @param string $field
     * @param string|int|null $value
     * @return int Count of deleted records.
     * @throws Exception
     */
    public function deleteByField(string $field, null|string|int $value): int
    {
        return $this->deleteByFields(
            [
                $field => $value
            ]
        );
    }

    /**
     * Delete database records by fields in key => value format.
     *
     * @param array $fields
     * @return int Count of deleted records.
     * @throws Exception
     */
    public function deleteByFields(array $fields): int
    {
        if (empty($fields)) {
            return 0;
        }

        $sql = $this->connection->delete((string)static::TABLE_NAME);
        /**
         * @var string $key
         * @var string|int|Date|null $value
         */
        foreach ($fields as $key => $value) {
            if ($value !== null && $value !== self::NOT_NULL) {
                $sql->where($key . ' = ?', $value);
            } elseif ($value == self::NOT_NULL) {
                $sql->where($key . ' IS NOT NULL');
            } else {
                $sql->where($key . ' IS NULL');
            }
        }
        $statement = $sql->execute();

        return $statement->rowCount();
    }

    /**
     * Delete record by Model. Uses primary key.
     *
     * @param BaseModel $record
     * @return int
     * @throws Exception
     */
    public function delete(BaseModel $record): int
    {
        /** @var string $primaryKey */
        $primaryKey = static::PRIMARY_KEY;
        /** @var string|int|null $recordPrimaryKey */
        $recordPrimaryKey = $record->$primaryKey ?? null;
        if ($recordPrimaryKey !== null) {
            $update = $this->connection->delete((string)static::TABLE_NAME)->where(
                $this->getEscapedFieldName($primaryKey) . ' = ?',
                $recordPrimaryKey
            );
            $statement = $update->execute();

            $this->onDelete($record);
            return $statement->rowCount();
        }

        return 0;
    }

    /**
     * This method is called when a Model is saved.
     *
     * Override this in the mapper to call actions on save.
     *
     * @param BaseModel $record
     * @param bool $new
     */
    protected function onSave(BaseModel $record, bool $new = true): void
    {
    }

    /**
     * This method is called when a Model is deleted.
     *
     * Override this in the mapper to call actions on deletion.
     *
     * @param BaseModel $record
     */
    protected function onDelete(BaseModel $record): void
    {
    }
}
