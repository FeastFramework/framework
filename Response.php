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
use Feast\ServiceContainer\ContainerException;
use Feast\ServiceContainer\NotFoundException;
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
    private object|array|null $jsonResponse = null;
    private ?int $jsonResponsePropertyTypes = null;
    private ?string $redirectPath = null;
    /** @var array<string,string> */
    private array $responseHeaders = [];

    /**
     * @throws ContainerException|NotFoundException
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
        http_response_code($this->responseCode->value);
    }

    /**
     * Send the appropriate response.
     *
     * @param View $view
     * @param RouterInterface $router
     * @param string $routePath
     * @throws JsonException
     * @throws ReflectionException
     */
    public function sendResponse(View $view, RouterInterface $router, string $routePath): void
    {
        $this->sendResponseCode();
        $this->sendHeaders();
        if ($this->getRedirectPath()) {
            header('Location:' . (string)$this->getRedirectPath());
            return;
        }
        if ($this->isJson()) {
            header('Content-type: application/json');
            if ($this->jsonResponse !== null) {
                echo Json::marshal($this->jsonResponse, $this->jsonResponsePropertyTypes);
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
     * @param object|array $response
     * @param int|null $jsonResponsePropertyTypes (see https://www.php.net/manual/en/class.reflectionproperty.php#reflectionproperty.constants.modifiers)
     */
    public function setJsonWithResponseObject(object|array $response, ?int $jsonResponsePropertyTypes = null): void
    {
        $this->jsonResponse = $response;
        $this->jsonResponsePropertyTypes = $jsonResponsePropertyTypes;
        $this->setJson();
    }

    /**
     * Set an HTTP header. Overrides previous version set.
     *
     * @param string $key
     * @param string $value
     * @return void
     */
    public function setHeader(string $key, string $value): void
    {
        $this->responseHeaders[$key] = $value;
    }

    protected function sendHeaders(): void
    {
        foreach ($this->responseHeaders as $key => $value) {
            header($key . ': ' . $value);
        }
    }

}
