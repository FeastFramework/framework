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

class Radio extends CheckboxAndRadio
{

    public const TYPE = 'radio';

    public function __construct(string $name, ?string $formName = null, ?string $id = null)
    {
        parent::__construct($name, $formName, self::TYPE, $id);
    }

    /**
     * Set selected value to true. Always overwrites.
     *
     * @param string $value
     * @param bool $overwrite
     * @return static
     */
    public function setValue(string $value, bool $overwrite = true): static
    {
        return parent::setValue($value);
    }

}
