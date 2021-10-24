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

namespace Feast;

use Feast\Enums\ResponseCode;
use Feast\Interfaces\ControllerInterface;
use Feast\Interfaces\ResponseInterface;
use Feast\Interfaces\RouterInterface;
use Feast\ServiceContainer\NotFoundException;
use Feast\ServiceContainer\ServiceContainer;
use stdClass;

/**
 * Class to manage controllers.
 */
abstract class HttpController implements ControllerInterface
{

    public stdClass $jsonEnabledActions;
    public ResponseInterface $response;

    /**
     * Initial creation of Controller
     *
     * @param ServiceContainer $di
     * @param View $view
     * @param ResponseInterface|null $response
     * @throws NotFoundException
     */
    public function __construct(public ServiceContainer $di, public View $view, ?ResponseInterface $response = null)
    {
        $this->jsonEnabledActions = new stdClass();
        $this->response = $response ?? $di->get(ResponseInterface::class);
    }

    /**
     * Initialize Controller - return false if not runnable for any reason.
     *
     * @return bool
     */
    public function init(): bool
    {
        return true;
    }

    /**
     * Forward to another action/controller/route.
     *
     * The preDispatch for plugins will NOT rerun. PostDispatch runs on the final action only.
     *
     * @param string|null $action
     * @param string|null $controller
     * @param array<string> $arguments
     * @param array<string> $queryString
     * @param string|null $module
     * @param string $route
     * @throws NotFoundException
     * @throws \Exception
     */
    public function forward(
        string $action = null,
        string $controller = null,
        array $arguments = [],
        array $queryString = [],
        ?string $module = null,
        string $route = ''
    ): void {
        /** @var RouterInterface $router */
        $router = $this->di->get(RouterInterface::class);
        $path = $router->getPath($action, $controller, $arguments, $queryString, $module, $route);

        $router->buildRouteForRequestUrl($path);
        $router->forward();
    }

    public function sendJsonResponse(object $responseObject): void
    {
        $this->response->setJsonWithResponseObject($responseObject);
    }

    /**
     * Check if an action should always be JSON.
     *
     * Defaults to false.
     *
     * @param string $actionName
     * @return bool
     */
    public function alwaysJson(string $actionName): bool
    {
        return false;
    }

    /**
     * Redirect to another action/controller/route.
     *
     * Sends a redirect after post dispatch plugins are ran.
     *
     * @param string|null $action
     * @param string|null $controller
     * @param array<string> $arguments
     * @param array<string> $queryString
     * @param string|null $module
     * @param string $route
     * @param int $code (30x)
     * @throws NotFoundException
     * @throws \Exception
     */
    public function redirect(
        ?string $action = null,
        ?string $controller = null,
        array $arguments = [],
        array $queryString = [],
        ?string $module = null,
        string $route = '',
        int $code = ResponseCode::HTTP_CODE_302
    ): void {
        $router = $this->di->get(RouterInterface::class);
        $response = $this->di->get(ResponseInterface::class);
        if ($route === '') {
            $route = $router->getRouteName();
        }
        $path = '/' . $router->getPath($action, $controller, $arguments, $queryString, $module, $route);
        $response->redirect($path, $code);
    }

    /**
     * Redirect to an external link after all post dispatch plugins finish.
     *
     * @param string $url The URL to redirect to
     * @param int $code Redirect code to use (default 302)
     * @throws NotFoundException
     */
    public function externalRedirect(string $url, int $code = ResponseCode::HTTP_CODE_302): void
    {
        $response = $this->di->get(ResponseInterface::class);
        $response->redirect($url, $code);
    }

    /**
     * Marks an action as being allowed to return a JSON object instead of output content.
     *
     * @param string $action
     */
    public function allowJsonForAction(string $action): void
    {
        $this->jsonEnabledActions->$action = true;
    }

    /**
     * Return if an action is allowed to return a JSON object.
     *
     * If false, format/json will have NO effect.
     *
     * @return bool
     * @throws NotFoundException
     */
    public function jsonAllowed(): bool
    {
        $router = $this->di->get(RouterInterface::class);
        $action = $router->getActionName();
        $actionReformatted = $router->getActionNameCamelCase();

        return $this->alwaysJson(
                $action
            ) || isset($this->jsonEnabledActions->$action) || isset($this->jsonEnabledActions->$actionReformatted);
    }

}
