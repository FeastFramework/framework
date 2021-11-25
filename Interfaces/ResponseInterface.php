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
use Feast\Enums\ResponseCode;
use Feast\ServiceContainer\ServiceContainerItemInterface;
use Feast\View;

/**
 * Manage HTTP Response codes.
 */
interface ResponseInterface extends ServiceContainerItemInterface
{
    final public const INTERFACE_NAME = self::class;

    /**
     * Set the response code.
     *
     * @param ResponseCode $responseCode
     * @throws Exception
     */
    public function setResponseCode(ResponseCode $responseCode): void;

    /**
     * Send http response header.
     */
    public function sendResponseCode(): void;

    /**
     * Send the appropriate response.
     *
     * @param View $view
     * @param RouterInterface $router
     * @param string $routePath
     */
    public function sendResponse(View $view, RouterInterface $router, string $routePath): void;

    /**
     * Check whether response is a JSON response.
     *
     * @return bool
     */
    public function isJson(): bool;

    /**
     * Mark response as JSON or not JSON.
     *
     * @param bool $isJson
     */
    public function setJson(bool $isJson = true): void;

    /**
     * Set redirect path.
     *
     * @param string $path
     * @param ResponseCode $code
     */
    public function redirect(string $path, ResponseCode $code = ResponseCode::HTTP_CODE_302): void;

    /**
     * Get the redirect path for a redirect.
     *
     * @return string|null
     */
    public function getRedirectPath(): ?string;

    /**
     * Mark the Response as a JSON response and send the passed in object.
     *
     * @param object $response
     */
    public function setJsonWithResponseObject(object $response): void;
}
