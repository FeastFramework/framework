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
use Feast\Form\Field;
use Feast\Form\Label;

class Text extends Field
{

    public function __construct(string $name, ?string $formName = null, string $type = 'text', ?string $id = null)
    {
        parent::__construct($name, $formName, $id, $type);
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
    public function toString(bool $showLabel = true, string $value = ''): string
    {
        $label = $this->buildLabelText();
        $output = $this->showLabel($showLabel, $this->label, Label::LABEL_POSITION_FIRST) ? $label : '';
        $output .= '<input type="' . $this->type . '" name="' . $this->name . '"';
        if ($this->default || $this->value) {
            $output .= ' value="' . ($this->value ?: $this->default) . '"';
        }
        $output .= $this->buildMetaData();
        /**
         * @var string $key
         * @var string $val
         */
        foreach ($this->meta as $key => $val) {
            $output .= ' ' . $key . '="' . $val . '"';
        }
        $output .= ' />';
        if ($this->showLabel($showLabel, $this->label, Label::LABEL_POSITION_LAST)) {
            $output .= $label;
        }

        return $output;
    }

    /**
     * Clear the value.
     * 
     * @return static
     */
    public function clearSelected(): static
    {
        $this->value = '';
        return $this;
    }

    /**
     * Set default value for the field.
     *
     * @param string $default
     * @return static
     */
    public function setDefault(string $default): static
    {
        $this->default = $default;

        return $this;
    }
    
    /**
     * Set the field value.
     * 
     * @param string $value
     * @param bool $overwrite - ignored
     * @return static
     */
    public function setValue(string $value, bool $overwrite = true): static
    {
        $this->value = $value;

        return $this;
    }

}
