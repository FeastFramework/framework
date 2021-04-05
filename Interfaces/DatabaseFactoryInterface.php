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

use Feast\Exception\ServerFailureException;
use Feast\ServiceContainer\ServiceContainerItemInterface;

/**
 * Manage the database connections.
 * Direct access is frowned upon, let the factory manage it for you.
 *
 */
interface DatabaseFactoryInterface extends ServiceContainerItemInterface
{
    public const INTERFACE_NAME = self::class;

    public const DEFAULT_CONNECTION = 'default';

    /**
     * Get the specified connection
     *
     * @param string $connection
     * @return DatabaseInterface
     * @throws ServerFailureException
     */
    public function getConnection(string $connection = self::DEFAULT_CONNECTION): DatabaseInterface;
}
