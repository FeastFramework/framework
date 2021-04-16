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

namespace Feast\Form\Field;

use Exception;
use Feast\Exception\InvalidArgumentException;
use Feast\Form\Field;
use Feast\Form\Label;
use stdClass;

abstract class CheckboxAndRadio extends Field
{

    public function __construct(
        string $name,
        ?string $formName,
        string $type,
        ?string $id = null
    ) {
        parent::__construct($name, $formName, $id, $type);
    }

    /**
     * Convert form field to string.
     *
     * @param bool $showLabel
     * @param string $value
     * @return string
     * @throws Exception
     */
    public function toString(bool $showLabel = true, string $value = ''): string
    {
        if (!isset($this->values[$value]) || $this->values[$value] instanceof Value === false) {
            throw new Exception('Invalid radio option');
        }
        $label = '';
        /** @var ?Label $labelData */
        $labelData = $this->values[$value]->label;
        if ($labelData) {
            $label = (string)$labelData;
        }
        $output = $this->showLabel($showLabel, $labelData, Label::LABEL_POSITION_FIRST) ? $label : '';
        $output .= $this->buildMainOutput($value);
        if ($this->showLabel($showLabel, $labelData, Label::LABEL_POSITION_LAST)) {
            $output .= $label;
        }

        return $output;
    }

    /**
     * Mark all values as not selected.
     *
     * @return static
     */
    public function clearSelected(): static
    {
        /** @var string $key */
        foreach (array_keys($this->values) as $key) {
            $this->values[$key]->selected = false;
        }
        return $this;
    }

    /**
     * Set selected value to true. If overwrite is true, set all others to false.
     *
     * @param string $value
     * @param bool $overwrite
     * @return static
     */
    public function setValue(string $value, bool $overwrite = true): static
    {
        /**
         * @var string $key
         * @var stdClass $val
         */
        foreach ($this->values as $key => $val) {
            if ($val->value == $value) {
                $this->values[$key]->selected = true;
            } elseif ($overwrite) {
                $this->values[$key]->selected = false;
            }
        }

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
        if (empty($value)) {
            throw new InvalidArgumentException('No value included');
        }
        $valueObject->id ??= (string)$this->formName . '_' . $this->name . '_' . $value;
        if ($valueObject->label instanceof Label && $valueObject->label->for === null) {
            $valueObject->label->for = $valueObject->id;
        }
        $this->values[$value] = $valueObject;

        return $this;
    }

    protected function buildMainOutput(string $value): string
    {
        /** @var Value $selectedValue */
        $selectedValue = $this->values[$value];
        $attributes = !empty($selectedValue->attributes) ? $selectedValue->attributes . ' ' : '';
        $id = $selectedValue->id !== null ? 'id="' . $selectedValue->id . '" ' : '';
        return '<input ' . $id . 'class="' . $selectedValue->class . '" type="' . $this->type . '" name="' . $this->name . '" value="' . $selectedValue->value . '"' . ($selectedValue->selected ? ' checked="checked"' : '') . ' ' . $attributes . '/>';
    }

    /**
     * @return array<string>
     */
    public function getValuesForValidation(): array
    {
        $return = [];
        foreach($this->values as $value) {
            if ( $value->selected ) {
                $return[] = $value->value;
            }
        }
        return $return;
    }

}
