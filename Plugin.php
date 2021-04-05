<?php

/**
 * Copyright 2021 Jeremy Presutti <Jeremy@Presutti.us>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

declare(strict_types=1);

namespace Feast;

use Feast\Interfaces\RouterInterface;

/**
 * This class is the base class for all plugins
 * Plugins have two methods. preDispatch and postDispatch.
 */
abstract class Plugin
{

    protected string $controller = '';
    protected string $action = '';
    protected string $module = '';

    /**
     * Initialize plugins.
     *
     * Run once for pre-dispatch and once for post-dispatch (ensures correct options, even if forwarded).
     *
     * @param RouterInterface $router
     */
    final public function init(RouterInterface $router): void
    {
        $this->module = $router->getModuleName();
        $this->controller = $router->getControllerName();
        $this->action = $router->getActionName();
    }

    public function __construct(protected View $view)
    {
    }

}
