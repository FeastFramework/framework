<?php

/**
 * Copyright 2021 Jeremy Presutti <Jeremy@Presutti.us>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
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

use Feast\Form\Label;

class Textarea extends Text
{

    public const TYPE = 'textarea';

    /**
     * Textarea constructor.
     *
     * @param string $name
     * @param string|null $formName
     * @param string|null $id
     */
    public function __construct(string $name, ?string $formName = null, ?string $id = null)
    {
        parent::__construct($name, $formName, self::TYPE, $id);
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
     * Convert form element to string.
     *
     * We do not use __toString() magic method because we wish to pass parameters.
     *
     * @param bool $showLabel
     * @param string $value - ignored
     * @return string
     */
    public function toString(
        bool $showLabel = true,
        string $value = ''
    ): string {
        $label = $this->buildLabelText();
        $output = $this->showLabel($showLabel, $this->label, Label::LABEL_POSITION_FIRST) ? $label : '';
        $output .= '<textarea name="' . $this->name . '"';
        $output .= $this->buildMetaData();
        /**
         * @var string $key
         * @var string $val
         */
        foreach ($this->meta as $key => $val) {
            $output .= ' ' . $key . '="' . $val . '"';
        }
        $output .= '>';
        if ($this->default || $this->value) {
            $output .= ($this->value ? $this->value : $this->default);
        }
        $output .= '</textarea>';
        if ($this->showLabel($showLabel, $this->label, Label::LABEL_POSITION_LAST)) {
            $output .= $label;
        }

        return $output;
    }

}
