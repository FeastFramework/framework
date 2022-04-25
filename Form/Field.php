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
use Feast\Form\Field\SelectValue;
use Feast\Form\Field\Value;
use Feast\Form\Filter\Filter;
use Feast\Form\Validator\Validator;

/**
 * This class is used to create form fields.
 */
abstract class Field
{

    public const LABEL_POSITION_FIRST = 'first';
    public const LABEL_POSITION_LAST = 'last';

    public string $class = '';
    public ?string $default = null;
    /** @var array<class-string> */
    public array $filter = [];
    public ?string $id = null;
    public ?Label $label = null;
    public ?string $placeholder = null;
    public bool $required = false;
    public string $style = '';
    /** @var array<class-string> */
    public array $validate = [];
    public string $value = '';
    /** @var array<Value|SelectValue> $values */
    public array $values = [];
    public array $meta = [];

    /**
     *
     * @param string $name
     * @param string|null $formName
     * @param string|null $id
     * @param string $type
     */
    public function __construct(
        public string $name,
        public ?string $formName = null,
        ?string $id = null,
        public string $type = 'text',
    ) {
        $this->id = !empty($id) ? $id : null;
    }

    /**
     * Set field ID
     *
     * If null is passed in, the value is auto generated from formName_fieldName
     *
     * @param string|null $id
     * @return static
     */
    public function setId(?string $id = null): static
    {
        $this->id = !empty($id) ? $id : (string)$this->formName . '_' . $this->name;
        return $this;
    }

    /**
     * Set the form name and generate field ID.
     *
     * @param string $formName
     * @return static
     */
    public function setFormNameAndGenerateId(string $formName): static
    {
        $this->formName = $formName;
        return $this->setId();
    }

    /**
     * Convert form element to string.
     *
     * We do not use __toString() magic method because we wish to pass parameters.
     *
     * @param bool $showLabel
     * @param string $value
     * @return string
     * @throws Exception
     */
    abstract public function toString(bool $showLabel = true, string $value = ''): string;

    protected function showLabel(bool $showLabel, ?Label $labelData, string $positionToCheck): bool
    {
        return $showLabel && $labelData && $labelData->position == $positionToCheck;
    }

    /** 
     * @throws ServerFailureException
     */
    public function __toString()
    {
        throw new ServerFailureException('__toString not enabled for ' . static::class . '. Use toString()');
    }

    /**
     * Clear the value.
     *
     * @return static
     */
    abstract public function clearSelected(): static;

    /**
     * Set a value on a field
     *
     * @param string $value
     * @param bool $overwrite - ignored for non select/checkbox
     * @return static
     */
    abstract public function setValue(string $value, bool $overwrite = true): static;

    /**
     * Add a class to the field.
     *
     * @param string $class
     * @return static
     */
    public function addClass(string $class): static
    {
        if (empty($this->class)) {
            $this->class = $class;
            return $this;
        }
        $classes = explode(' ', $this->class);
        if (!in_array($class, $classes)) {
            $classes[] = $class;
        }
        $this->class = implode(' ', $classes);

        return $this;
    }

    /**
     * Remove a class from the field.
     *
     * @param string $class
     * @return static
     */
    public function removeClass(string $class): static
    {
        $classes = explode(' ', $this->class);
        $location = array_search($class, $classes);
        while ($location !== false) {
            unset($classes[$location]);
            $location = array_search($class, $classes);
        }
        $this->class = implode(' ', $classes);

        return $this;
    }

    /**
     * Set the class string for the field.
     *
     * @param string $class
     * @return static
     */
    public function setClass(string $class): static
    {
        $this->class = $class;

        return $this;
    }

    /**
     * Add meta data for the field.
     *
     * This data is output in key="value" for the toString call.
     *
     * @param string $key
     * @param string $value
     * @return static
     */
    public function addMeta(string $key, string $value): static
    {
        $this->meta[$key] = $value;

        return $this;
    }

    /**
     * Add a filter on the field. Filters must be classes that implement the Filter interface.
     *
     * @param class-string $filter
     * @return static
     * @see Filter
     */
    public function addFilter(string $filter): static
    {
        $this->filter[] = $filter;

        return $this;
    }

    /**
     * Set default value for the field.
     *
     * @param string $default
     * @return static
     * @throws InvalidArgumentException
     */
    public function setDefault(string $default): static
    {
        throw new InvalidArgumentException('Cannot set a default value on ' . static::class . ' objects.');
    }

    /**
     * Set Label for the field.
     *
     * @param Label $label
     * @return static
     */
    public function setLabel(Label $label): static
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Set placeholder text.
     *
     * @param string $placeholder
     * @return static
     */
    public function setPlaceholder(string $placeholder): static
    {
        $this->placeholder = $placeholder;

        return $this;
    }

    /**
     * Set required.
     *
     * @param bool $required
     * @return static
     */
    public function setRequired(bool $required = true): static
    {
        $this->required = $required;

        return $this;
    }

    /**
     * Set style attribute for the field's toString call.
     *
     * @param string $style
     * @return static
     */
    public function setStyle(string $style): static
    {
        $this->style = $style;

        return $this;
    }

    /**
     * Add a validator to the field. Validator must implement the Validator interface.
     *
     * @param class-string $validation
     * @return static
     * @see Validator
     */
    public function addValidator(string $validation): static
    {
        $this->validate[] = $validation;

        return $this;
    }

    /**
     * Add a value or select value to the field.
     *
     * @param string $value
     * @param Value|SelectValue $valueObject
     * @return static
     * @throws InvalidArgumentException
     */
    public function addValue(string $value, Value|SelectValue $valueObject): static
    {
        throw new InvalidArgumentException('Cannot add a value on ' . static::class . ' objects.');
    }

    protected function buildLabelText(): string
    {
        if ($this->label !== null) {
            return (string)$this->label;
        }
        return '';
    }

    protected function buildMetaData(): string
    {
        $output = '';
        if ($this->class) {
            $output .= ' class="' . $this->class . '"';
        }
        if ($this->id) {
            $output .= ' id="' . $this->id . '"';
        }
        if ($this->placeholder) {
            $output .= ' placeholder="' . $this->placeholder . '"';
        }
        if ($this->style) {
            $output .= ' style="' . $this->style . '"';
        }
        if ($this->required) {
            $output .= ' required="required"';
        }

        return $output;
    }

    public function getValuesForValidation(): string|array
    {
        return $this->value;
    }

}
