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

namespace Feast\Attributes;

use Attribute;
use Feast\Enums\RequestMethod;

#[Attribute(Attribute::TARGET_METHOD)]
class Path
{
    final public const METHOD_GET = 1;
    final public const METHOD_POST = 2;
    final public const METHOD_PUT = 4;
    final public const METHOD_DELETE = 8;
    final public const METHOD_PATCH = 16;
    final public const METHOD_ALL = self::METHOD_GET | self::METHOD_POST | self::METHOD_PUT | self::METHOD_DELETE | self::METHOD_PATCH;

    /**
     * Path constructor.
     *
     * @param string $path
     * @param string $name
     * @param int $method
     * @param array<string|bool> $defaults
     */
    public function __construct(
        public string $path,
        public string $name,
        public int $method = self::METHOD_ALL,
        public array $defaults = []
    ) {
    }

    /**
     * @return array<RequestMethod>
     */
    public function getMethods(): array
    {
        $return = [];
        if ($this->method & self::METHOD_GET) {
            $return[] = RequestMethod::GET;
        }
        if ($this->method & self::METHOD_POST) {
            $return[] = RequestMethod::POST;
        }
        if ($this->method & self::METHOD_PUT) {
            $return[] = RequestMethod::PUT;
        }
        if ($this->method & self::METHOD_DELETE) {
            $return[] = RequestMethod::DELETE;
        }
        if ($this->method & self::METHOD_PATCH) {
            $return[] = RequestMethod::PATCH;
        }
        return $return;
    }


}
