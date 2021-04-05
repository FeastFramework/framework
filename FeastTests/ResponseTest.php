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

use Feast\Enums\ResponseCode;
use Feast\Exception\ResponseException;
use Feast\Response;
use PHPUnit\Framework\TestCase;

class ResponseTest extends TestCase
{
    public function testResponse(): void
    {
        $response = new Response();
        $response->sendResponse();
        $this->assertTrue(Feast\ResponseMock::$code === 200);
    }

    public function testUpdateResponse(): void
    {
        $response = new Response();
        $response->setResponseCode(ResponseCode::HTTP_CODE_404);
        $response->sendResponse();
        $this->assertTrue(Feast\ResponseMock::$code === 404);
    }

    public function testInvalidResponse(): void
    {
        $response = new Response();
        $this->expectException(ResponseException::class);
        $response->setResponseCode(99999);
    }

    public function testIsJson(): void
    {
        $request = new Response();
        $this->assertFalse($request->isJson());
        $request->setJson();
        $this->assertTrue($request->isJson());
    }

    public function testRedirect(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $response = new Response();
        $response->redirect('/redirecting');
        $this->assertEquals('/redirecting', $response->getRedirectPath());
    }

}
