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

namespace Feast\Session;

use Feast\Exception\ResponseException;
use Feast\Exception\SessionNotStartedException;
use Feast\Interfaces\ConfigInterface;
use Feast\Interfaces\ResponseInterface;
use Feast\ServiceContainer\ContainerException;
use Feast\ServiceContainer\NotFoundException;
use Feast\ServiceContainer\ServiceContainerItemInterface;
use Feast\Traits\DependencyInjected;
use stdClass;

/**
 * Manage session variables. Also handles session security if "strictIp" setting
 * is enabled.
 */
class Session implements ServiceContainerItemInterface
{
    use DependencyInjected;

    private bool $sessionEnabled = true;

    /**
     * Initial creation of Feast_Session.
     *
     * If Strict IP setting is enabled, the session is destroyed if the IP doesn't match.
     *
     * @param ConfigInterface $config
     * @throws ContainerException
     * @throws NotFoundException
     */
    public function __construct(ConfigInterface $config)
    {
        $this->checkInjected();
        /** @var bool $isEnabled */
        $isEnabled = $config->getSetting('session.enabled', true);
        if ($isEnabled === false) {
            $this->sessionEnabled = false;
            return;
        }
        session_name((string)$config->getSetting('session.name', 'Feast_Session'));
        session_set_cookie_params((int)$config->getSetting('session.timeout', 0));
        session_start();
        $Feast = $this->getNamespace('Feast');
        /** @var bool $strictIp */
        $strictIp = $config->getSetting('session.strictIp', false);
        if ($strictIp) {
            if (isset($Feast->ipAddress) && $Feast->ipAddress !== (string)$_SERVER['REMOTE_ADDR']) {
                session_destroy();
                $response = di(ResponseInterface::class);

                $response->redirect((string)$_SERVER['REQUEST_URI']);
            }
        }
        $Feast->ipAddress = (string)$_SERVER['REMOTE_ADDR'];
    }

    /**
     * Return session namespace by name. Creates if non-existent.
     *
     * @param string $namespace
     * @return stdClass
     * @throws SessionNotStartedException
     */
    public function getNamespace(string $namespace): stdClass
    {
        if ($this->sessionEnabled === false) {
            throw new SessionNotStartedException('Session not started',);
        }
        if (!isset($_SESSION[$namespace]) || $_SESSION[$namespace] instanceof stdClass === false) {
            $_SESSION[$namespace] = new stdClass();
        }

        return $_SESSION[$namespace];
    }

    /**
     * Destroy a namespace in the session.
     *
     * @param string $namespace
     * @throws SessionNotStartedException
     */
    public function destroyNamespace(string $namespace): void
    {
        if ($this->sessionEnabled === false) {
            throw new SessionNotStartedException('Session not started',);
        }
        unset($_SESSION[$namespace]);
    }
    
    public function isEnabled(): bool
    {
        return $this->sessionEnabled;
    }

}
