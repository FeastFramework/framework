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

use Exception;
use Feast\Attributes\Path;
use Feast\Enums\RequestMethod;
use Feast\ServiceContainer\NotFoundException;
use Feast\ServiceContainer\ServiceContainerItemInterface;

interface RouterInterface extends ServiceContainerItemInterface
{
    public const INTERFACE_NAME = self::class;

    /**
     * Update the "runAs". Needed to properly route in case of cached router.
     *
     * @param string $runAs
     */
    public function setRunAs(string $runAs): void;

    /**
     * Get the "runAs".
     *
     * @return string
     */
    public function getRunAs(): string;

    /**
     * Get route based on the passed in arguments.
     *
     * @param string $arguments
     * @return void
     * @throws NotFoundException
     */
    public function buildRouteForRequestUrl(string $arguments): void;

    /**
     * Get route for CLI based on the passed in arguments.
     *
     * @param string $arguments
     * @return void
     */
    public function buildCliArguments(string $arguments): void;

    /**
     * Get fully qualified class name for controller class.
     *
     * @return class-string
     */
    public function getControllerFullyQualifiedName(): string;

    /**
     * Get the action method name for the desired action by analyzing class.
     *
     * @return string
     */
    public function getActionMethodName(): string;

    /**
     *
     * Get URL path for passed in arguments.
     *
     * @param string|null $action
     * @param string|null $controller
     * @param array<string> $arguments
     * @param array<string> $queryString
     * @param string|null $module
     * @param string $route
     * @param string|null $requestMethod
     * @return string
     * @throws Exception
     */
    public function getPath(
        ?string $action = null,
        ?string $controller = null,
        array $arguments = [],
        array $queryString = [],
        ?string $module = null,
        string $route = '',
        ?string $requestMethod = null
    ): string;

    /**
     * Get controller class.
     *
     * @return string
     */
    public function getControllerClass(): string;

    /**
     * Get action method.
     *
     * @return string
     */
    public function getAction(): string;

    /**
     * Get controller name.
     *
     * @return string
     */
    public function getControllerName(): string;

    /**
     * Get action name.
     *
     * @return string
     */
    public function getActionName(): string;

    /**
     * Get action name in camel case (for controller actions).
     *
     * Eg: show-data = showData.
     *
     * @return string
     */
    public function getActionNameCamelCase(): string;

    /**
     * Get controller name camel-case (for views and urls).
     *
     * Eg: showData = show-data
     *
     * @return string
     */
    public function getControllerNameCamelCase(): string;

    /**
     * Get action name with dashes (for views and urls).
     *
     * Eg: showData = show-data.
     *
     * @return string
     */
    public function getActionNameDashes(): string;

    /**
     * Get module name.
     *
     * @return string
     */
    public function getModuleName(): string;

    /**
     * Get route name.
     *
     * @return string
     */
    public function getRouteName(): string;

    /**
     * Get whether the current request has been forwarded to a different controller.
     *
     * If this is true, the controller loop in Main will rerun with the new parameters.
     *
     * @return bool
     */
    public function forwarded(): bool;

    /**
     * Set whether to reloop through the controller loop.
     *
     * Controller::forward will trigger this automatically.
     *
     * @param bool $loop
     * @see HttpController::forward()
     */
    public function forward(bool $loop = true): void;

    /**
     * Get route's default arguments.
     *
     * @param string $route
     * @param string $requestMethod
     * @return array|null
     * @throws Exception
     */
    public function getDefaultArguments(string $route, string $requestMethod): ?array;

    /**
     * Create new route. Using the Attributes is easier.
     *
     * @param string $fullPath
     * @param string $controller
     * @param string $action
     * @param string|null $routeName
     * @param array<string|bool> $defaults
     * @param string $httpMethod
     * @param string $module
     * @throws Exception
     * @see Path
     */
    public function addRoute(
        string $fullPath,
        string $controller,
        string $action,
        ?string $routeName = null,
        array $defaults = null,
        string $httpMethod = 'GET',
        string $module = 'Default'
    ): void;

    /**
     * Assign new arguments for route.
     *
     * @param array $arguments
     * @param array<string> $params
     * @param bool $clearArguments
     * @param bool $allowVariadic
     */
    public function assignArguments(
        array $arguments,
        array $params = [],
        bool $clearArguments = false,
        bool $allowVariadic = false
    ): void;

    /**
     * Dynamically build the routes from the Path attributes.
     */
    public function buildRoutes(): void;

    /**
     * Check if router instance is from cached object.
     *
     * @return bool
     */
    public function isFromCache(): bool;

    /**
     * Cache the router object.
     */
    public function cache(): void;
}
