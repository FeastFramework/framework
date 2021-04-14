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

use Feast\Exception\InvalidOptionException;
use Feast\Exception\NotFoundException;
use Feast\Interfaces\ConfigInterface;
use Feast\Interfaces\HttpRequestInterface;

abstract class Service
{

    protected HttpRequestInterface $httpRequest;

    public function __construct()
    {
        /** @var ConfigInterface $config */
        $config = \di(ConfigInterface::class);
        /** @var ?class-string $serviceClass */
        $serviceClass = $config->getSetting('service.class');
        if ($serviceClass === null) {
            throw new NotFoundException('HTTP Request class must be specified in config. See service.class');
        } elseif (is_subclass_of($serviceClass, HttpRequestInterface::class, true)) {
            $this->httpRequest = new $serviceClass();
        } else {
            throw new InvalidOptionException('HTTP Request class must implement HttpRequestInterface');
        }
    }

    /**
     * Get the underlying HttpRequest object for the Service class.
     * 
     * @return HttpRequestInterface|null
     */
    public function getHttpRequestObject(): ?HttpRequestInterface
    {
        return $this->httpRequest;
    }

}
