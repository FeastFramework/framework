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

use Exception;
use Feast\Collection\Collection;
use Feast\Collection\CollectionList;
use Feast\Enums\ResponseCode;
use SimpleXMLElement;
use stdClass;

class Response
{

    protected CollectionList $headers;
    protected ?string $contentType = null;
    protected ?string $contentTypeHeader = null;
    public function __construct(protected string $rawResponse, protected ResponseCode $responseCode)
    {
        $this->headers = new CollectionList('string');
    }
    
    public function addHeader(string $name, string $value): void
    {
        /** @var string|null $currentValue */
        $currentValue = $this->headers->get(strtolower($name));
        if ( $currentValue !== null ) {
            $value = $currentValue . ', ' . $value;
        }
        $this->headers->add(strtolower($name), $value);
    }
    
    public function getHeader(string $name): string|null
    {
        if ( strtolower($name) === 'content-type' ) {
            return $this->getContentType();
        }
        /** @var string|null */
        return $this->headers->get(strtolower($name));
    }
    
    public function getContentType(): string|null
    {
        return $this->contentType;
    }
    
    public function getContentTypeHeader(): string|null
    {
        return $this->contentTypeHeader;
    }
    
    public function parseContentType(string $contentType): void
    {
        [$contentTypeShort] = explode(';', $contentType);
        $this->contentType = $contentTypeShort;
        $this->contentTypeHeader = $contentType;
    }

    public function getResponseAsText(): string
    {
        return $this->rawResponse;
    }

    public function getResultAsJson(): stdClass|array|null
    {
        try {
            /** @var stdClass */
            return json_decode(
                       mb_convert_encoding($this->rawResponse, 'UTF-8', ['ISO-8859-1', 'UTF-8']),
                flags: JSON_THROW_ON_ERROR
            );
        } catch (Exception) {
            return null;
        }
    }

    public function getResultAsXml(): ?SimpleXMLElement
    {
        try {
            return new SimpleXMLElement($this->rawResponse, LIBXML_NOERROR | LIBXML_NOWARNING);
        } catch (Exception) {
            return null;
        }
    }
    
    public function getResponseCode(): ResponseCode
    {
        return $this->responseCode;
    }
}
