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

namespace Feast\HttpRequest;

use CurlHandle;
use Feast\Enums\RequestMethod;
use Feast\Enums\ResponseCode;
use Feast\Exception\BadRequestException;
use Feast\Exception\ServerFailureException;
use Feast\Interfaces\HttpRequestInterface;
use SimpleXMLElement;

/**
 * Abstract Class to make HTTP/HTTPS requests
 * Extended in both a curl and file_get_contents based class
 *
 * @todo add separate class for response
 */
abstract class HttpRequest implements HttpRequestInterface
{
    public const CONTENT_TYPE_JSON = 'application/json';
    public const DEFAULT_USER_AGENT = 'Mozilla/5.0 (X11; Linux x86_64) FeastFramework/1.0';

    protected RequestMethod $method;
    /** @var array<string,string> $cookies */
    protected array $cookies;
    protected array $arguments;
    protected string $baseUrl = '';
    protected string $language;
    protected ?string $username = null;
    protected ?string $password = null;
    protected ?Response $response = null;
    protected ?ResponseCode $responseCode = null;
    protected string $userAgent;
    protected ?string $referer = null;
    protected ?string $contentType = null;
    protected array $headers = [];

    public function __construct(protected ?string $url = null)
    {
        $this->cookies = [];
        $this->arguments = [];
        $this->language = 'en';
        $this->userAgent = self::DEFAULT_USER_AGENT;
        $this->method = RequestMethod::GET;
    }

    /**
     * Initialize a get request and set the url.
     *
     * @param string $url
     * @return HttpRequest
     * @throws ServerFailureException
     */
    public function get(string $url): HttpRequestInterface
    {
        $this->setMethod(RequestMethod::GET);
        $this->contentType = null;
        $this->clearArguments();
        $this->setUrl($url);

        return $this;
    }

    /**
     * Initialize a post request and set the url.
     *
     * @param string $url
     * @return HttpRequest
     * @throws ServerFailureException
     */
    public function post(string $url): HttpRequestInterface
    {
        $this->setMethod(RequestMethod::POST);
        $this->contentType = 'application/x-www-form-urlencoded';
        $this->setUrl($url);

        return $this;
    }

    /**
     * Initialize a put request and set the url.
     *
     * @param string $url
     * @return HttpRequest
     * @throws ServerFailureException
     */
    public function put(string $url): HttpRequestInterface
    {
        $this->setMethod(RequestMethod::PUT);
        $this->setUrl($url);

        return $this;
    }

    /**
     * Initialize a patch request and set the url.
     *
     * @param string $url
     * @return HttpRequest
     * @throws ServerFailureException
     */
    public function patch(string $url): HttpRequestInterface
    {
        $this->setMethod(RequestMethod::PATCH);
        $this->setUrl($url);

        return $this;
    }

    /**
     * Initialize a delete request and set the url.
     *
     * @param string $url
     * @return HttpRequest
     * @throws ServerFailureException
     */
    public function delete(string $url): HttpRequestInterface
    {
        $this->setMethod(RequestMethod::DELETE);
        $this->setUrl($url);

        return $this;
    }

    /**
     * Set request method.
     *
     * @param RequestMethod $method
     */
    private function setMethod(RequestMethod $method): void
    {
        $this->contentType = null;
        $this->arguments = [];
        $this->method = $method;
    }

    /**
     * Add Cookie.
     *
     * @param string $name
     * @param string $value
     * @return HttpRequest
     */
    public function addCookie(string $name, string $value): HttpRequestInterface
    {
        $this->cookies[$name] = $value;

        return $this;
    }

    /**
     * Add an argument to the request.
     *
     * @param string $name
     * @param string $value
     * @param bool $array
     * @return HttpRequest
     */
    public function addArgument(string $name, string $value, bool $array = false): HttpRequestInterface
    {
        if ($array === false) {
            $this->arguments[$name] = $value;
        } else {
            /** @var array $item */
            $item = $this->arguments[$name] ?? [];
            $item[] = $value;
            $this->arguments[$name] = $item;
        }

        return $this;
    }

    /**
     * Clear all arguments on request.
     *
     * @return static
     */
    public function clearArguments(): HttpRequestInterface
    {
        $this->arguments = [];

        return $this;
    }

    /**
     * Set exact arguments for the request
     *
     * Useful for JSON requests.
     *
     * @param array $arguments
     * @return HttpRequestInterface
     */
    public function setArguments(array $arguments): HttpRequestInterface
    {
        $this->arguments = $arguments;
        return $this;
    }

    /**
     * Add multiple arguments to the request
     *
     * Allows only for simple key => value mappings, not array values.
     *
     * @param array $arguments
     * @param bool $array
     * @return HttpRequest
     */
    public function addArguments(array $arguments, bool $array = false): HttpRequestInterface
    {
        /**
         * @var string $key
         * @var string|array $val
         */
        foreach ($arguments as $key => $val) {
            if (is_array($val)) {
                /** @var string $subVal */
                foreach ($val as $subVal) {
                    $this->addArgument($key, $subVal, true);
                }
                continue;
            }
            $this->addArgument($key, $val, $array);
        }

        return $this;
    }

    /**
     * Get all arguments.
     *
     * @return array
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    /**
     * Set the url for the request.
     *
     * @param string $url
     * @return HttpRequest
     * @throws ServerFailureException
     */
    private function setUrl(string $url): HttpRequestInterface
    {
        $this->url = $url;
        if (!str_contains($url, 'http://') && !str_contains($url, 'https://')) {
            throw new BadRequestException('Invalid URL passed to HttpRequest::setURL');
        }
        if (substr_count($url, '/') >= 3) {
            $slashLocation = strpos($url, '/', 8);
            if ($slashLocation === false) {
                // This would be a malformed url.
                throw new BadRequestException('Error: couldn\'t reliably determine url');
            }
            $this->baseUrl = substr($url, 0, $slashLocation);
        } else {
            $this->baseUrl = $url;
        }

        return $this;
    }

    /**
     * Get url for request.
     *
     * @return string
     */
    public function getUrl(): string
    {
        return $this->baseUrl;
    }

    /**
     * Get request method.
     *
     * @return RequestMethod
     */
    public function getMethod(): RequestMethod
    {
        return $this->method;
    }

    /**
     * Set the user agent for the request.
     *
     * @param string $userAgent
     * @return HttpRequest
     */
    public function setUserAgent(string $userAgent): HttpRequestInterface
    {
        $this->userAgent = $userAgent;

        return $this;
    }

    /**
     * Get the user agent for the request.
     *
     * @return string
     */
    public function getUserAgent(): string
    {
        return $this->userAgent;
    }

    /**
     * Authenticate a request.
     *
     * @param string $username
     * @param string $password
     * @return HttpRequest
     */
    public function authenticate(string $username, string $password): HttpRequestInterface
    {
        $this->username = $username;
        $this->password = $password;

        return $this;
    }

    /**
     * Get authentication string in user:password format.
     *
     * @return string|null
     */
    public function getAuthenticationString(): ?string
    {
        if ($this->username === null || $this->password === null) {
            return null;
        }
        return $this->username . ':' . $this->password;
    }

    /**
     * Initialize a post request and set as JSON.
     *
     * @param string|null $url
     * @return static
     * @throws ServerFailureException
     */
    public function postJson(string $url = null): HttpRequestInterface
    {
        $this->method = RequestMethod::POST;
        if ($url !== null) {
            $this->post($url);
        }
        $this->contentType = self::CONTENT_TYPE_JSON;

        return $this;
    }

    /**
     * Initialize a put request and set as JSON.
     *
     * @param string|null $url
     * @return static
     * @throws ServerFailureException
     */
    public function putJson(string $url = null): HttpRequestInterface
    {
        $this->method = RequestMethod::PUT;
        if ($url !== null) {
            $this->put($url);
        }
        $this->contentType = self::CONTENT_TYPE_JSON;

        return $this;
    }

    /**
     * Initialize a patch request and set as JSON.
     *
     * @param string|null $url
     * @return static
     * @throws ServerFailureException
     */
    public function patchJson(string $url = null): HttpRequestInterface
    {
        $this->method = RequestMethod::PATCH;
        if ($url !== null) {
            $this->patch($url);
        }
        $this->contentType = self::CONTENT_TYPE_JSON;

        return $this;
    }

    /**
     * Make a request and store the response.
     *
     * @return HttpRequest
     */
    abstract public function makeRequest(): HttpRequestInterface;

    /**
     * Parse HTTP Headers for cookies.
     *
     * @param array<string> $responseHeaders
     */
    protected function parseResponseHeaders(array $responseHeaders): void
    {
        if (empty($responseHeaders)) {
            $this->responseCode = ResponseCode::HTTP_CODE_500;
            return;
        }
        $status = explode(' ', $responseHeaders[0]);
        $responseCode = !empty($status[1]) ? (int)$status[1] : ResponseCode::HTTP_CODE_500->value;
        /**
         * @var ResponseCode
         * @psalm-suppress UndefinedMethod
         */
        $this->responseCode = ResponseCode::tryFrom($responseCode) ?? ResponseCode::HTTP_CODE_500;

        foreach ($responseHeaders as $header) {
            $headerData = explode(': ', $header);
            if (strtolower($headerData[0]) === 'set-cookie') {
                $this->parseCookie($headerData[1]);
            }
        }
    }

    /**
     * Parse cookie and add Cookie object if not expired.
     *
     * @param string $cookie
     * @return void
     */
    protected function parseCookie(string $cookie): void
    {
        $cookieData = new Cookie($cookie);

        if ($cookieData->isExpired()) {
            return;
        }

        /**
         * @var string $key
         * @var string $val
         */
        foreach ($cookieData->getData() as $key => $val) {
            $this->addCookie($key, $val);
        }
    }

    /**
     * Get the request result as a string.
     *
     * @return string
     */
    public function getResponseAsString(): string
    {
        if ($this->response === null) {
            return '';
        }
        return $this->response->getResponseAsText();
    }

    /**
     * Get the request result as a json object.
     *
     * @return \stdClass|null
     */
    public function getResponseAsJson(): ?\stdClass
    {
        if ($this->response === null) {
            return null;
        }
        return $this->response->getResultAsJson();
    }

    /**
     * Get the request result as an XML Object.
     *
     * @return SimpleXMLElement|null
     */
    public function getResponseAsXml(): ?SimpleXMLElement
    {
        if ($this->response === null) {
            return null;
        }
        return $this->response->getResultAsXml();
    }

    /**
     * Get the \Feast\Response object for a finished request.
     * @return Response|null
     */
    public function getResponse(): ?Response
    {
        return $this->response;
    }

    /**
     * Get cookies from the request.
     *
     * @return array
     */
    public function getCookies(): array
    {
        return $this->cookies;
    }

    /**
     * Set the referer [sic] for the request.
     *
     * @param string|null $referer
     * @return HttpRequest
     */
    public function setReferer(?string $referer): HttpRequestInterface
    {
        $this->referer = $referer;

        return $this;
    }

    /**
     * Get the referer [sic] for the request.
     *
     * @return string|null
     */
    public function getReferer(): ?string
    {
        return $this->referer;
    }

    /**
     * Get the http response code for the request.
     *
     * @return ResponseCode|null
     */
    public function getResponseCode(): ?ResponseCode
    {
        return $this->responseCode;
    }

    /**
     * Get content type for request.
     *
     * @return string|null
     */
    public function getContentType(): ?string
    {
        return $this->contentType;
    }

    /**
     * Add header by key/value.
     *
     * @param string $header
     * @param string $value
     * @return static
     */
    public function addHeader(string $header, string $value): HttpRequestInterface
    {
        $this->headers[$header] = $value;

        return $this;
    }

    /**
     * Get all request headers.
     *
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Get curl handle for curl requests. Throws exception if used on a simple request.
     *
     * @return CurlHandle
     * @throws ServerFailureException
     */
    public function getCurl(): CurlHandle
    {
        throw new ServerFailureException('Attempted to retrieve a Curl Handle on a non Curl HttpRequest');
    }
}
