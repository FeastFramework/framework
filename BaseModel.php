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

use Feast\Exception\InvalidOptionException;
use Feast\Exception\NotFoundException;

/**
 * Base model class
 * Getter and setter throws an exception to prevent adding new properties to the model
 */
abstract class BaseModel
{
    protected const MAPPER_NAME = null;
    protected ?BaseModel $originalModel = null;

    public function __set(string $name, mixed $value): void
    {
        throw new InvalidOptionException('Invalid option for model', 500);
    }

    public function __get(string $name): void
    {
        throw new InvalidOptionException('Invalid option for model', 500);
    }

    /**
     * Get the difference between the original model and the current model.
     *
     * @param BaseModel $oldModel
     * @return array
     */
    public function getChanges(BaseModel $oldModel): array
    {
        $changes = [];
        /** @var array<string|int|null|Date> $fields */
        $fields = get_object_vars($this);
        unset($fields['originalModel']);

        /**
         * @var string $key
         */
        foreach ($fields as $key => $val) {
            if ($val != $oldModel->$key) {
                $changes[] = $this->getChangeField($oldModel, $key);
            }
        }

        return $changes;
    }

    /**
     * Clone the current model and mark as original.
     */
    public function makeOriginalModel(): void
    {
        $this->originalModel = null;
        $this->originalModel = clone $this;
    }

    /**
     * Get original model.
     *
     * @return BaseModel|null
     */
    public function getOriginalModel(): ?BaseModel
    {
        return $this->originalModel;
    }

    /**
     * Get changelog data for field on model.
     *
     * @param BaseModel $oldModel
     * @param string $field
     * @return string
     */
    public function getChangeField(BaseModel $oldModel, string $field): string
    {
        if (str_ends_with(strtolower($field), 'encrypted')) {
            return ucfirst(str_replace('_', ' ', $field)) . ' Changed';
        }

        return ucfirst(
                str_replace(
                    '_',
                    ' ',
                    $field
                )
            ) . ' Changed From "' . (string)$oldModel->$field . '" to "' . (string)$this->$field . '"';
    }

    /**
     * Save the current model.
     *
     * @throws NotFoundException
     */
    public function save(): void
    {
        /** @var ?class-string<BaseMapper> $mapperName */
        $mapperName = static::MAPPER_NAME;
        if ($mapperName === null) {
            throw new NotFoundException('Cannot save model - mapper not specified');
        }
        $mapper = new $mapperName();

        $mapper->save($this);
    }
}
