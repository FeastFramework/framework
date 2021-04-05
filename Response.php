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

use Feast\Enums\ResponseCode;
use Feast\Exception\ResponseException;
use Feast\Interfaces\ResponseInterface;
use Feast\ServiceContainer\ServiceContainerItemInterface;
use Feast\Traits\DependencyInjected;

/**
 * Manage HTTP Response.
 */
class Response implements ServiceContainerItemInterface, ResponseInterface
{
    use DependencyInjected;

    private int $responseCode = ResponseCode::HTTP_CODE_200;
    private bool $isJson = false;
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
     * @param int $responseCode
     * @throws ResponseException
     */
    public function setResponseCode(int $responseCode): void
    {
        if (ResponseCode::isValidResponseCode($responseCode)) {
            $this->responseCode = $responseCode;
        } else {
            throw new ResponseException('Invalid response code!');
        }
    }

    /**
     * Send http response header.
     */
    public function sendResponse(): void
    {
        http_response_code($this->responseCode);
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
     * @param int $code
     * @throws ResponseException
     */
    public function redirect(string $path, int $code = 302): void
    {
        $this->redirectPath = $path;
        $this->setResponseCode($code);
    }

}
