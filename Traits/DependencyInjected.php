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

namespace Feast\Traits;

use Feast\ServiceContainer\ContainerException;
use Feast\ServiceContainer\ServiceContainer;

trait DependencyInjected
{
    /**
     * Check if the instantiated item is already in the Service Container. Throws if item found.
     * 
     * @param mixed ...$arguments
     * @throws ContainerException
     * @throws \Feast\ServiceContainer\NotFoundException
     */
    public function checkInjected(mixed ...$arguments): void
    {
        $className = defined(static::class . '::INTERFACE_NAME') ? (string)static::INTERFACE_NAME : static::class;
        if (di(ServiceContainer::class)->has($className, ...$arguments)) {
            throw new ContainerException('Attempted to instantiate class that is in DI Container' . $className);
        }
    }
}
