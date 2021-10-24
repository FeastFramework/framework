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
use Feast\Exception\CurlException;
use Feast\Interfaces\HttpRequestInterface;

class Curl extends HttpRequest implements HttpRequestInterface
{
    /** @var CurlHandle $curl */
    protected mixed $curl;

    /**
     * @throws CurlException
     */
    public function __construct(?string $url = null)
    {
        parent::__construct($url);
        $this->resetCurl($url ?? '');
    }

    /**
     * Get curl handle.
     *
     * @return CurlHandle
     */
    public function getCurl(): CurlHandle
    {
        return $this->curl;
    }

    /**
     * Reset curl handle for request.
     *
     * @param string $url
     * @return static
     * @throws CurlException
     */
    protected function resetCurl(string $url): static
    {
        $this->curl = curl_init($url);
        if ($this->curl === false) {
            throw new CurlException('Error initializing Curl');
        }
        return $this;
    }

    /**
     * Make the HTTP request and store the result
     */
    public function makeRequest(): HttpRequestInterface
    {
        $url = $this->url ?? '';
        curl_setopt($this->curl, CURLOPT_URL, $url);
        curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, $this->method);
        curl_setopt($this->curl, CURLOPT_USERAGENT, $this->userAgent);
        $header = [];
        $header[] = 'Accept-language: ' . $this->language;
        if (count($this->cookies) !== 0) {
            $cookie = '';
            /**
             * @var string $name
             * @var string $value
             */
            foreach ($this->cookies as $name => $value) {
                $cookie .= $name . '=' . $value . '; ';
            }
            curl_setopt($this->curl, CURLOPT_COOKIE, $cookie);
        }
        if (count($this->arguments) !== 0) {
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
                    curl_setopt($this->curl, CURLOPT_POSTFIELDS, $arguments);
                    if ($this->contentType) {
                        $header[] = 'Content-Type: ' . $this->contentType;
                    }
                    $header[] = 'Content-Length: ' . (string)strlen($arguments);
                    break;
            }
        }

        /**
         * @var string $headerKey
         * @var string $headerVal
         */
        foreach ($this->headers as $headerKey => $headerVal) {
            $header[] = $headerKey . ': ' . $headerVal;
        }

        if ($this->username && $this->password) {
            curl_setopt($this->curl, CURLOPT_USERPWD, $this->getAuthenticationString());
        }
        if ($this->referer) {
            curl_setopt($this->curl, CURLOPT_REFERER, $this->referer);
        }
        curl_setopt($this->curl, CURLOPT_URL, $url);
        curl_setopt($this->curl, CURLOPT_HTTPHEADER, $header);
        curl_setopt($this->curl, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($this->curl, CURLOPT_HEADER, 1);
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($this->curl);
        if (is_bool($response)) {
            $response = '';
        }
        $headerSize = (int)curl_getinfo($this->curl, CURLINFO_HEADER_SIZE);
        $header = substr($response, 0, $headerSize);
        $this->parseResponseHeaders(explode("\n", $header));
        $this->response = new Response(
            substr($response, $headerSize),
            ($this->responseCode ?? ResponseCode::HTTP_CODE_500)
        );
        curl_close($this->curl);

        return $this;
    }

}
