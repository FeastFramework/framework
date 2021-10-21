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
use Feast\Interfaces\ResponseInterface;
use Feast\Interfaces\RouterInterface;
use Feast\ServiceContainer\ServiceContainerItemInterface;
use Feast\Traits\DependencyInjected;
use JsonException;
use ReflectionException;

/**
 * Manage HTTP Response.
 */
class Response implements ServiceContainerItemInterface, ResponseInterface
{
    use DependencyInjected;

    private ResponseCode $responseCode = ResponseCode::HTTP_CODE_200;
    private bool $isJson = false;
    private object|null $jsonResponse = null;
    private ?string $redirectPath = null;

    /**
     * Initialize class
     */
    public function __construct()
    {
        $this->checkInjected();
    }

    /**
     * Set the response code.
     *
     * @param ResponseCode $responseCode
     */
    public function setResponseCode(ResponseCode $responseCode): void
    {
        $this->responseCode = $responseCode;
 
    }

    /**
     * Send http response header.
     */
    public function sendResponseCode(): void
    {
        /** @psalm-suppress UndefinedPropertyFetch */
        http_response_code((int)$this->responseCode->value);
    }

    /**
     * Send the appropriate response.
     *
     * @param View $view
     * @param RouterInterface $router
     * @param string $routePath
     * @throws JsonException|ReflectionException
     */
    public function sendResponse(View $view, RouterInterface $router, string $routePath): void
    {
        $this->sendResponseCode();
        if ($this->getRedirectPath()) {
            header('Location:' . (string)$this->getRedirectPath());
            return;
        }
        if ($this->isJson()) {
            header('Content-type: application/json');
            if ($this->jsonResponse !== null) {
                echo Json::marshal($this->jsonResponse);
            } else {
                echo json_encode($view, JSON_THROW_ON_ERROR, 4096);
            }
        } else {
            $view->showView(
                ucfirst($router->getControllerNameCamelCase()),
                $router->getActionNameDashes(),
                $routePath
            );
        }
    }

    /**
     * Check whether response is a JSON response.
     *
     * @return bool
     */
    public function isJson(): bool
    {
        return $this->isJson;
    }

    /**
     * Mark response as JSON or not JSON.
     *
     * @param bool $isJson
     */
    public function setJson(bool $isJson = true): void
    {
        $this->isJson = $isJson;
        if ($isJson === false) {
            $this->jsonResponse = null;
        }
    }

    /**
     * Get the redirect path for a redirect.
     *
     * @return string|null
     */
    public function getRedirectPath(): ?string
    {
        return $this->redirectPath;
    }

    /**
     * Set redirect path.
     *
     * @param string $path
     * @param ResponseCode $code
     */
    public function redirect(string $path, ResponseCode $code = ResponseCode::HTTP_CODE_302): void
    {
        $this->redirectPath = $path;
        $this->setResponseCode($code);
    }

    /**
     * Mark the Response as a JSON response and send the passed in object.
     *
     * @param object $response
     */
    public function setJsonWithResponseObject(object $response): void
    {
        $this->jsonResponse = $response;
        $this->setJson();
    }

}
