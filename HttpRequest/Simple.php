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

use Feast\Enums\RequestMethod;
use Feast\Enums\ResponseCode;
use Feast\Exception\InvalidArgumentException;
use Feast\Exception\ServerFailureException;
use Feast\Interfaces\HttpRequestInterface;

class Simple extends HttpRequest implements HttpRequestInterface
{

    /**
     * Make an HTTP/HTTPS request and return the result
     *
     * @return Simple
     * @throws ServerFailureException
     */
    public function makeRequest(): Simple
    {
        if ($this->url === null) {
            throw new InvalidArgumentException('No url specified for request');
        }
        $url = $this->url;
        $context = [];
        $context['method'] = $this->method->value;
        $context['user_agent'] = $this->userAgent;
        $context['request_fulluri'] = true;
        $header = 'Accept-language: ' . $this->language . "\r\n";

        $this->addCookiesToHeader($header);
        $this->buildArgumentsForRequest($url, $header, $context);
        if ($this->username && $this->password) {
            $header .= 'Authorization: Basic ' . base64_encode($this->username . ':' . $this->password) . "\r\n";
        }
        if ($this->referer) {
            $header .= 'Referer: ' . $this->referer . "\r\n";
        }
        /**
         * @var string $headerKey
         * @var string $headerVal
         */
        foreach ($this->headers as $headerKey => $headerVal) {
            $header .= $headerKey . ': ' . $headerVal . "\r\n";
        }
        $context['header'] = $header;
        /** @var array<string> $http_response_header */
        $this->parseResponseHeaders($http_response_header ?? []);
        $this->response = new Response(file_get_contents($url, false, stream_context_create(['http' => $context])),($this->getResponseCode() ??  ResponseCode::HTTP_CODE_500));

        return $this;
    }

    protected function addCookiesToHeader(string &$header): void
    {
        foreach ($this->cookies as $name => $value) {
            $header .= 'Cookie: ' . $name . '=' . $value . "\r\n";
        }
    }

    protected function buildArgumentsForRequest(string &$url, string &$header, array &$context): void
    {
        if (count($this->arguments) != 0) {
            if ($this->contentType == self::CONTENT_TYPE_JSON) {
                $arguments = json_encode($this->arguments);
            } else {
                $arguments = http_build_query($this->arguments);
            }
            switch ($this->method) {
                case RequestMethod::GET:
                case RequestMethod::DELETE:
                    $url .= '?' . $arguments;
                    break;
                case RequestMethod::POST:
                case RequestMethod::PUT:
                case RequestMethod::PATCH:
                    $header .= 'Content-Type: ' . (string)$this->contentType . "\r\n";
                    $header .= 'Content-Length: ' . (string)strlen($arguments) . "\r\n";
                    $context['content'] = $arguments;
            }
        }
    }

}
