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

class Select extends Field
{

    public const TYPE = 'select';

    public function __construct(string $name, ?string $formName = null, ?string $id = null)
    {
        parent::__construct($name, $formName, $id, self::TYPE);
    }

    /**
     * Convert form element to string.
     *
     * We do not use __toString() magic method because we wish to pass parameters.
     *
     * @param bool $showLabel
     * @param string $value - ignored
     * @return string
     * @throws Exception
     */
    public function toString(
        bool $showLabel = true,
        string $value = ''
    ): string {
        $label = $this->buildLabelText();
        $output = $this->showLabel($showLabel, $this->label, Label::LABEL_POSITION_FIRST) ? $label : '';
        $id = $this->id !== null ? 'id="' . $this->id . '" ' : '';
        $output .= '<select ' . $id . 'name="' . $this->name . '" style="' . $this->style . '" class="' . $this->class . '">';
        /** @var Value $val */
        foreach ($this->values as $val) {
            $output .= '<option value="' . $val->value . '"';
            $output .= $val->selected ? ' selected="selected"' : '';
            $output .= '>' . (string)$val->label . '</option>' . "\n";
        }
        $output .= '</select>';
        if ($this->showLabel($showLabel, $this->label, Label::LABEL_POSITION_LAST)) {
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
        $values = $this->values;
        /** @var string $key */
        foreach (array_keys($values) as $key) {
            /** @var Value $valueItem */
            $valueItem = $values[$key];
            $valueItem->selected = false;
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
       
        $this->values[$value] = $valueObject;

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
        $values = $this->values;
        /**
         * @var string $key
         * @var Value $val
         */
        foreach ($values as $key => $val) {
            /** @var Value $valueItem */
            $valueItem = $values[$key];
            if ($val->value === $value) {
                $valueItem->selected = true;
            } elseif ($overwrite) {
                $valueItem->selected = false;
            }
        }
        $this->values = $values;

        return $this;
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
