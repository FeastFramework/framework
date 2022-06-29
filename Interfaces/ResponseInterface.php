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
use Feast\Exception\ResponseException;
use Feast\ServiceContainer\ServiceContainerItemInterface;
use Feast\View;

/**
 * Manage HTTP Response codes.
 */
interface ResponseInterface extends ServiceContainerItemInterface
{
    public const INTERFACE_NAME = self::class;

    /**
     * Set the response code.
     *
     * @param int $responseCode
     * @throws ResponseException
     */
    public function setResponseCode(int $responseCode): void;

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
     * @throws \JsonException|\ReflectionException
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
     * @param int $code
     * @throws ResponseException
     */
    public function redirect(string $path, int $code = 302): void;

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
     * @param int|null $jsonResponsePropertyTypes (see https://www.php.net/manual/en/class.reflectionproperty.php#reflectionproperty.constants.modifiers)
     */
    public function setJsonWithResponseObject(object $response, ?int $jsonResponsePropertyTypes = null): void;

    /**
     * Set an HTTP header. Overrides previous version set.
     *
     * @param string $key
     * @param string $value
     * @return void
     */
    public function setHeader(string $key,string $value): void;

}
