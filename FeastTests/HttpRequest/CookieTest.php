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

namespace HttpRequest;

use Feast\HttpRequest\Cookie;
use PHPUnit\Framework\TestCase;

class CookieTest extends TestCase
{

    public function testIsExpiredTrue(): void
    {
        $cookie = $this->getCookie();
        $this->assertTrue($cookie->isExpired());
    }

    public function testIsExpiredFalse(): void
    {
        $cookie = $this->getCookie('0');
        $this->assertFalse($cookie->isExpired());
    }

    public function testCreate(): void
    {
        $cookie = $this->getCookie();
        $this->assertInstanceOf(Cookie::class,$cookie);
    }

    public function testGetData(): void
    {
        $cookie = $this->getCookie();
        $data = $cookie->getData();
        $this->assertTrue($data['user'] === 'feast');
    }

    protected function getCookie(string $expires = '1'): Cookie
    {
        return new Cookie(
            'user=feast; path=/; secure=true; httponly=true; domain=feast-framework.com; expires=' . $expires
        );
    }
}
