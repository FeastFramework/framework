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

namespace Feast\Config;

use Feast\Interfaces\FeatureFlagInterface;

/**
 *
 * Class to manage feature flags.
 */
class FeatureFlag implements FeatureFlagInterface
{
    public function __construct(protected bool $isEnabled)
    {
    }

    /**
     * Returns true if the feature should be enabled, false otherwise
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->isEnabled;
    }

}
