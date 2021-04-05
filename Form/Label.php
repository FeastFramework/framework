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

namespace Feast\Form;

class Label
{

    public const LABEL_POSITION_FIRST = 'first';
    public const LABEL_POSITION_LAST = 'last';

    public function __construct(
        public string $text = '',
        public ?string $for = null,
        public ?string $class = null,
        public ?string $id = null,
        public ?string $style = null,
        public ?string $attributes = null,
        public string $position = self::LABEL_POSITION_FIRST
    ) {
    }

    /**
     * Get label string.
     * 
     * @return string
     */
    public function __toString(): string
    {
        $return = '<label';
        if ($this->for !== null) {
            $return .= ' for="' . $this->for . '"';
        }
        if ($this->class !== null) {
            $return .= ' class="' . $this->class . '"';
        }
        if ($this->id !== null) {
            $return .= ' id="' . $this->id . '"';
        }
        if ($this->style !== null) {
            $return .= ' style="' . $this->style . '"';
        }
        if ($this->attributes !== null) {
            $return .= ' ' . $this->attributes;
        }
        $return .= '>' . $this->text . '</label>';
        return $return;
    }

    /**
     * Set label text.
     * 
     * @param string $text
     * @return static
     */
    public function setText(string $text): static
    {
        $this->text = $text;

        return $this;
    }

    /**
     * Set label class.
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
     * Set label id.
     * 
     * @param string $id
     * @return static
     */
    public function setId(string $id): static
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Set label style.
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
     * Set label for. 
     * 
     * @param string $for
     * @return static
     */
    public function setFor(string $for): static
    {
        $this->for = $for;

        return $this;
    }

    /**
     * Set attributes for label. This is output as is.
     * @param string $attributes
     * @return static
     */
    public function setAttributes(string $attributes): static
    {
        $this->attributes = $attributes;

        return $this;
    }

    /**
     * Set label position.
     * 
     * @see Label::LABEL_POSITION_FIRST 
     * @see Label::LABEL_POSITION_LAST
     * @param string $position
     * @return static
     */
    public function setPosition(string $position): static
    {
        $this->position = $position;

        return $this;
    }

}
