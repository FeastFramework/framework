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

namespace Feast\Form;

use Exception;
use Feast\Exception\InvalidArgumentException;
use Feast\Exception\ServerFailureException;
use Feast\Form\Filter\Filter;
use Feast\Form\Validator\Validator;
use stdClass;

abstract class Form
{

    protected stdClass $fields;
    protected array $files = [];
    protected array $errors = [];

    public const ERROR_NOT_SET = 'Required';

    /**
     * @param string $name
     * @param string|null $action
     * @param string $method
     * @param array $attributes
     */
    public function __construct(
        protected string $name,
        protected ?string $action = null,
        protected string $method = 'GET',
        protected array $attributes = []
    ) {
        $this->fields = new stdClass();
    }

    /**
     * Set form action.
     *
     * @param string $action
     * @return static
     */
    public function setAction(string $action): static
    {
        $this->action = $action;

        return $this;
    }

    /**
     * Get field by name.
     *
     * @param string $name
     * @return Field
     * @throws InvalidArgumentException
     */
    public function getField(string $name): Field
    {
        /** @var Field|null $field */
        $field = $this->fields->$name ?? null;
        if ($field === null) {
            throw new InvalidArgumentException('Unknown field ' . $name . ' on form ' . $this->name);
        }

        return $field;
    }

    /**
     * Add field to form.
     *
     * @param Field $field
     * @return Field
     */
    public function addField(
        Field $field
    ): Field {
        $name = $field->name;
        if (!isset($field->formName)) {
            $field->setFormNameAndGenerateId($this->name);
        }
        $this->fields->$name = $field;

        return $field;
    }

    /**
     * Validate form via the validators on each field.
     *
     * @param bool $validatePartial
     * @return bool
     */
    public function isValid(bool $validatePartial = false): bool
    {
        $formValid = true;
        $errors = [];
        /**
         * @psalm-suppress RawObjectIteration
         * @var string $key
         * @var Field $formField
         */
        foreach ($this->fields as $key => $formField) {
            /** @var array|string $field */
            $field = $formField->getValuesForValidation();
            if ($this->isRequiredAndMissing($validatePartial, $formField, $field)) {
                $errors[] = [$key, self::ERROR_NOT_SET];
                $formValid = false;
            } elseif ($this->notRequiredAndIsEmpty($field, $formField)) {
                continue;
            } elseif (is_string($field)) {
                /** @var Validator $validator */
                foreach ($formField->validate as $validator) {
                    $formValid = $validator::validate($key, $field, $formField, $this->files, $errors, $formValid);
                }
            }
        }
        $this->errors = $errors;

        return $formValid;
    }

    /**
     * Validate form partial.
     *
     * Alias for isValid(true);
     *
     * @return bool
     */
    public function isValidPartial(): bool
    {
        return $this->isValid(true);
    }

    /**
     * Filter fields based on a predefined set of rules.
     *
     * @param array $fields
     */
    public function filter(array $fields): void
    {
        /**
         * @psalm-suppress RawObjectIteration
         * @var string $key
         * @var Field $val
         */
        foreach ($this->fields as $key => $val) {
            if (!isset($fields[$key])) {
                continue;
            }
            /** @var string $field */
            $field = $fields[$key];
            /** @var Filter $filter */
            foreach ($val->filter as $filter) {
                $field = $filter::filter($field);
            }

            $val->setValue($field);
        }
    }

    /**
     * Return a form field for output.
     *
     * @param string $fieldName
     * @param bool $showLabel
     * @param string $value
     * @return string
     * @throws ServerFailureException|Exception
     */
    public function displayField(string $fieldName, bool $showLabel = true, string $value = ''): string
    {
        /** @var ?Field $field */
        $field = $this->fields->$fieldName ?? null;
        if (!isset($field)) {
            throw new InvalidArgumentException('No such field!');
        }

        return $field->toString($showLabel, $value);
    }

    /**
     * Print form opening tag.
     *
     * @return string
     */
    public function openForm(): string
    {
        $action = $this->action ? ' action="' . $this->action . '"' : '';
        $form = '<form id="' . $this->name . '"' . $action . ' method="' . $this->method . '"';
        /**
         * @var string $key
         * @var string $val
         */
        foreach ($this->attributes as $key => $val) {
            $form .= ' ' . $key . '="' . $val . '"';
        }
        $form .= '>';

        return $form;
    }

    /**
     * Print form closing tag.
     *
     * @return string
     */
    public function closeForm(): string
    {
        return '</form>';
    }

    /**
     * Set form field value.
     *
     * @param string $fieldName
     * @param string $value
     * @param bool $overwrite
     * @throws InvalidArgumentException
     */
    public function setValue(string $fieldName, string $value = '', bool $overwrite = true): void
    {
        /** @var ?Field $field */
        $field = $this->fields->$fieldName ?? null;
        if (!isset($field)) {
            throw new InvalidArgumentException('No such field!');
        }
        $field->setValue($value, $overwrite);
    }

    /**
     * Set all values for the form.
     *
     * @param array $values
     */
    public function setAllValues(array $values): void
    {
        /**
         * @var string $key
         * @var string|array $val
         */
        foreach ($values as $key => $val) {
            /** @var ?Field $field */
            $field = $this->fields->$key ?? null;
            if (!isset($field) || $field->type === 'file') {
                continue;
            }

            if (is_array($val)) {
                $field->clearSelected();

                /** @var string $theVal */
                foreach ($val as $theVal) {
                    $field->setValue($theVal, false);
                }
            } else {
                $field->setValue($val);
            }
        }
    }

    /**
     * Set all files for the form.
     */
    public function setAllFiles(): static
    {
        /**
         * @var string $key
         * @var array $file
         */
        foreach ($_FILES as $key => $file) {
            $this->files[$key] = $file;
        }

        return $this;
    }

    /**
     * Return a file from the form.
     *
     * @param string $key
     * @return array|null
     */
    public function getFile(string $key): ?array
    {
        return isset($this->files[$key]) ? (array)$this->files[$key] : null;
    }

    /**
     * Get all errors from the form.
     *
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    protected function isRequiredAndMissing(bool $validatePartial, Field $formField, array|string|null $field): bool
    {
        if ($validatePartial || !$formField->required) {
            return false;
        }

        return ($field === null || (!is_array($field) && trim($field) === '') || (is_array($field) && empty($field)));
    }

    protected function notRequiredAndIsEmpty(array|string $field, Field $formField): bool
    {
        return !is_array($field) && !$formField->required && trim($field) === '';
    }

}
