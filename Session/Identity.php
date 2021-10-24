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

use Feast\BaseModel;
use Feast\Interfaces\ConfigInterface;
use Feast\ServiceContainer\ContainerException;
use Feast\ServiceContainer\NotFoundException;
use Feast\ServiceContainer\ServiceContainerItemInterface;
use Feast\Traits\DependencyInjected;
use stdClass;

/**
 * Class to manage an identity. This class is a convenience shorthand to the Feast_Login namespace
 */
class Identity implements ServiceContainerItemInterface
{
    use DependencyInjected;

    protected stdClass $me;

    /**
     * @throws ContainerException|NotFoundException
     */
    public function __construct(protected ConfigInterface $config, protected Session $session)
    {
        $this->checkInjected();
        $this->me = $session->getNamespace('Feast_Login');
    }

    /**
     * Get the identify of a user.
     *
     * @return BaseModel|null
     */
    public function getUser(): ?BaseModel
    {
        return isset($this->me->identity) && $this->me->identity instanceof BaseModel ? $this->me->identity : null;
    }

    /**
     * Save a user identity to the session.
     *
     * @param BaseModel $user
     */
    public function saveUser(BaseModel $user): void
    {
        $this->me->identity = $user;
    }

    /**
     * Destroy the Feast_Login namespace.
     */
    public function destroyUser(): void
    {
        $this->session->destroyNamespace('Feast_Login');
        $this->me = $this->session->getNamespace('Feast_Login');
    }

}
