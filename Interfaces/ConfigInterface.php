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

use Feast\ServiceContainer\ServiceContainerItemInterface;

/**
 *
 * Class to load and manage the configuration files.
 */
interface ConfigInterface extends ServiceContainerItemInterface
{
    public const INTERFACE_NAME = self::class;

    /**
     * Cache the config and store on disk
     */
    public function cacheConfig(): void;

    /**
     * Get current environment
     *
     * @return string
     */
    public function getEnvironmentName(): string;

    /**
     * Get config setting. Returns default if setting not found.
     *
     * The Config key can be a parent value or nested via "." separation
     * If a "." is in the key, the settings will be fetched recursively.
     * The default will be returned if any key in the path is not found.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getSetting(string $key, mixed $default = null): mixed;
}
