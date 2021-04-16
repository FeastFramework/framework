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

class Cookie
{
    private int $expires = 0;
    private ?string $domain = null;
    private ?string $path = null;
    private bool $secure = false;
    private bool $httpOnly = false;
    private array $cookieData = [];

    public function __construct(string $cookie)
    {
        $cookieData = explode(';', $cookie);
        foreach ($cookieData as $info) {
            $cookieInfo = explode('=', $info);
            $key = $cookieInfo[0];
            $val = $cookieInfo[1] ?? '';
            switch (strtolower(trim($key))) {
                case 'expires':
                    $this->expires = (int)trim($val);
                    break;
                case 'domain':
                    $this->domain = trim($val);
                    break;
                case 'path':
                    $this->path = trim($val);
                    break;
                case 'secure':
                    $this->secure = true;
                    break;
                case 'httponly':
                    $this->httpOnly = true;
                    break;
                default:
                    $this->cookieData[trim($key)] = trim($val);
            }
        }
    }

    /**
     * Check if cookie is expired.
     *
     * @return bool
     */
    public function isExpired(): bool
    {
        return $this->expires != 0 && $this->expires < time();
    }

    /**
     * Get all cookie data for request.
     *
     * @return array
     */
    public function getData(): array
    {
        return $this->cookieData;
    }
}
