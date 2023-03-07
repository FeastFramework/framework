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

namespace Feast\Database;

use Feast\Enums\ResponseCode;
use Feast\Exception\DatabaseException;
use Feast\Exception\ServerFailureException;
use Feast\Interfaces\ConfigInterface;
use Feast\Interfaces\DatabaseFactoryInterface;
use Feast\Interfaces\DatabaseInterface;
use Feast\Interfaces\LoggerInterface;
use Feast\ServiceContainer\ContainerException;
use Feast\ServiceContainer\NotFoundException;
use Feast\ServiceContainer\ServiceContainerItemInterface;
use Feast\Traits\DependencyInjected;
use PDO;
use stdClass;

use function di;

/**
 * Manages database connections.
 * Direct access to Database class is incorrect usage.
 * Let the factory handle connections.
 */
class DatabaseFactory implements ServiceContainerItemInterface, DatabaseFactoryInterface
{
    use DependencyInjected;

    private stdClass $connections;
    private ConfigInterface $config;

    /**
     * Get or initialize and return the specified connection.
     *
     * @param string $connection
     * @param LoggerInterface|null $logger
     * @return DatabaseInterface
     * @throws DatabaseException
     * @throws NotFoundException
     * @throws ServerFailureException
     * @throws \Feast\Exception\InvalidOptionException
     */
    public function getConnection(string $connection = self::DEFAULT_CONNECTION, ?LoggerInterface $logger = null): DatabaseInterface
    {
        if (isset($this->connections->$connection)) {
            /** @var DatabaseInterface */
            return $this->connections->$connection;
        }
        /** @var stdClass|null $connectionConfig */
        $connectionConfig = $this->config->getSetting('database.' . $connection);
        /** @var class-string $connectionClass */
        $connectionClass = $this->config->getSetting('pdoClass', PDO::class);
        if ($connectionConfig !== null) {
            $logger ??= di(LoggerInterface::INTERFACE_NAME);
            $connectionObject = new Database($connectionConfig, $connectionClass, $logger);
            $this->connections->$connection = $connectionObject;

            return $connectionObject;
        }
        throw new DatabaseException('Invalid Database Specified', ResponseCode::HTTP_CODE_500, 500);
    }

    /**
     * Initial creation of DatabaseFactory.
     *
     * @param ConfigInterface|null $config
     * @param stdClass|null $connections
     * @throws ContainerException
     * @throws NotFoundException
     */
    public function __construct(ConfigInterface $config = null, stdClass $connections = null)
    {
        $this->checkInjected();
        $this->config = $config ?? di(ConfigInterface::class);
        $this->connections = $connections ?? new stdClass();
    }

}
