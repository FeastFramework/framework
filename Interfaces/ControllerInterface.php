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

namespace Feast\Interfaces;

/**
 *
 * Class to load and manage the configuration files.
 */
interface ControllerInterface
{
    /**
     * Initialize Controller - return false if not runnable for any reason.
     *
     * @return bool
     */
    public function init(): bool;

    /**
     * Check if an action should always be JSON.
     *
     * Defaults to false.
     *
     * @param string $actionName
     * @return bool
     */
    public function alwaysJson(string $actionName): bool;
}
