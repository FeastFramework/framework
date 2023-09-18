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

namespace Feast\Attributes;

use Attribute;
use Feast\Date;

#[Attribute(Attribute::TARGET_PROPERTY)]
class JsonItem
{
    /**
     * JsonItem constructor.
     *
     * @param string|null $name
     * @param string|null $arrayOrCollectionType
     * @param string $dateFormat - Only used if the actual property type is a Date. This will specify the format it should be converted to in the json string.
     * @param bool $included - If included is false, JSON strings from the decorated object will not include this property.
     * @param bool $omitEmpty - If omitEmpty is true, JSON strings from the decorated object will skip this property if an empty string or null are detected.
     * @param string|null $timezoneId
     */
    public function __construct(
        public ?string $name = null,
        public ?string $arrayOrCollectionType = null,
        public string $dateFormat = Date::ATOM,
        public bool $included = true,
        public bool $omitEmpty = false,
        public ?string $timezoneId = null
    ) {
    }
}
