<?php

/**
 * Copyright 2022 Jeremy Presutti <Jeremy@Presutti.us>
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

#[Attribute(Attribute::TARGET_METHOD)]
class AccessControl
{
    /**
     * AccessControl constructor.
     *
     * @param list<string>|null $disabledEnvironments
     * @param list<string>|null $onlyEnvironments
     */
    public function __construct(
        public ?array $disabledEnvironments = null,
        public ?array $onlyEnvironments = null,
    ) {
    }

    public function isEnabled(string $environment): bool
    {
        if ($this->onlyEnvironments !== null && !in_array($environment, $this->onlyEnvironments)) {
            return false;
        }

        if ($this->disabledEnvironments !== null && in_array($environment, $this->disabledEnvironments)) {
            return false;
        }

        return true;
    }

}
