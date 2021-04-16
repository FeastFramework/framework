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

use CurlHandle;
use Feast\Exception\ServerFailureException;
use Feast\HttpRequest\HttpRequest;
use Feast\HttpRequest\Response;
use SimpleXMLElement;
use stdClass;

interface HttpRequestInterface
{

    /**
     * Add header by key/value.
     *
     * @param string $header
     * @param string $value
     * @return static
     */
    public function addHeader(string $header, string $value): HttpRequestInterface;

    /**
     * Get the http response code for the request.
     *
     * @return int|null
     */
    public function getResponseCode(): ?int;

    /**
     * Get cookies from the request.
     *
     * @return array
     */
    public function getCookies(): array;

    /**
     * Get the request result as an XML Object.
     *
     * @return SimpleXMLElement|null
     */
    public function getResponseAsXml(): ?SimpleXMLElement;

    /**
     * Get the request result as a json object.
     *
     * @return \stdClass|null
     */
    public function getResponseAsJson(): ?stdClass;

    /**
     * Get the request result as a string.
     *
     * @return string
     */
    public function getResponseAsString(): string;

    public function makeRequest(): HttpRequestInterface;

    /**
     * Initialize a post request and set as JSON.
     *
     * @param string|null $url
     * @return static
     * @throws ServerFailureException
     */
    public function postJson(string $url = null): HttpRequestInterface;

    /**
     * Initialize a put request and set as JSON.
     *
     * @param string|null $url
     * @return HttpRequestInterface
     * @throws ServerFailureException
     */
    public function putJson(string $url = null): HttpRequestInterface;

    /**
     * Initialize a patch request and set as JSON.
     *
     * @param string|null $url
     * @return HttpRequestInterface
     * @throws ServerFailureException
     */
    public function patchJson(string $url = null): HttpRequestInterface;

    /**
     * Authenticate a request.
     *
     * @param string $username
     * @param string $password
     * @return HttpRequest
     */
    public function authenticate(string $username, string $password): HttpRequestInterface;

    /**
     * Set the user agent for the request.
     *
     * @param string $userAgent
     * @return HttpRequest
     */
    public function setUserAgent(string $userAgent): HttpRequestInterface;

    /**
     * Initialize a get request and set the url.
     *
     * @param string $url
     * @return HttpRequest
     * @throws ServerFailureException
     */
    public function get(string $url): HttpRequestInterface;

    /**
     * Initialize a post request and set the url.
     *
     * @param string $url
     * @return HttpRequest
     * @throws ServerFailureException
     */
    public function post(string $url): HttpRequestInterface;

    /**
     * Initialize a put request and set the url.
     *
     * @param string $url
     * @return HttpRequestInterface
     */
    public function put(string $url): HttpRequestInterface;

    /**
     * Initialize a patch request and set the url.
     *
     * @param string $url
     * @return HttpRequest
     * @throws ServerFailureException
     */
    public function patch(string $url): HttpRequestInterface;

    /**
     * Initialize a delete request and set the url.
     *
     * @param string $url
     * @return HttpRequestInterface
     */
    public function delete(string $url): HttpRequestInterface;

    /**
     * Add Cookie.
     *
     * @param string $name
     * @param string $value
     * @return HttpRequestInterface
     */
    public function addCookie(string $name, string $value): HttpRequestInterface;

    /**
     * Add an argument to the request.
     *
     * @param string $name
     * @param string $value
     * @param bool $array
     * @return HttpRequestInterface
     */
    public function addArgument(string $name, string $value, bool $array = false): HttpRequestInterface;

    /**
     * Clear all arguments on request.
     *
     * @return static
     */
    public function clearArguments(): HttpRequestInterface;

    /**
     * Set exact arguments for the request
     *
     * Useful for JSON requests.
     *
     * @param array $arguments
     * @return HttpRequestInterface
     */
    public function setArguments(array $arguments): HttpRequestInterface;
    
    /**
     * Add multiple arguments to the request
     * Allows only for simple key => value mappings, not array values.
     *
     * @param array $arguments
     * @return HttpRequestInterface
     */
    public function addArguments(array $arguments): HttpRequestInterface;

    /**
     * Set the referer [sic] for the request.
     *
     * @param string|null $referer
     * @return HttpRequest
     */
    public function setReferer(?string $referer): HttpRequestInterface;

    /**
     * Get the referer [sic] for the request.
     *
     * @return string|null
     */
    public function getReferer(): ?string;

    /**
     * Get content type for request.
     *
     * @return string|null
     */
    public function getContentType(): ?string;

    /**
     * Get all request headers.
     *
     * @return array
     */
    public function getHeaders(): array;

    /**
     * Get curl handle for curl requests. Throws exception if used on a simple request.
     *
     * @return CurlHandle
     */
    public function getCurl(): CurlHandle;

    /**
     * Get the \Feast\Response object for a finished request.
     * @return Response|null
     */
    public function getResponse(): ?Response;

}
