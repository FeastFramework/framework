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

use Feast\Enums\ResponseCode;
use Feast\Response;
use PHPUnit\Framework\TestCase;

class ResponseTest extends TestCase
{

    public function setUp(): void
    {
        di(null, \Feast\Enums\ServiceContainer::CLEAR_CONTAINER);
    }

    public function testResponseCode(): void
    {
        $response = new Response();
        $response->sendResponseCode();
        $this->assertTrue(Feast\ResponseMock::$code === 200);
    }

    public function testUpdateResponse(): void
    {
        $response = new Response();
        $response->setResponseCode(ResponseCode::HTTP_CODE_404);
        $response->sendResponseCode();
        $this->assertTrue(Feast\ResponseMock::$code === 404);
    }

    public function testInvalidResponse(): void
    {
        $response = new Response();
        $this->expectException(TypeError::class);
        $response->setResponseCode(99999);
    }

    public function testSendResponseOutputRedirect(): void
    {
        $response = new Response();
        $response->redirect('Test');
        $response->sendResponse(
            $this->createStub(\Feast\View::class),
            $this->createStub(\Feast\Interfaces\RouterInterface::class),
            ''
        );
        $output = $this->getActualOutputForAssertion();
        $this->assertEquals('Location:Test', $output);
        $this->assertEquals(302, \Feast\ResponseMock::$code);
    }

    public function testSendResponseOutputJson(): void
    {
        $response = new Response();
        $response->setJson();
        $response->sendResponse(
            $this->createStub(\Feast\View::class),
            $this->createStub(\Feast\Interfaces\RouterInterface::class),
            ''
        );
        $output = $this->getActualOutputForAssertion();
        $this->assertEquals('Content-type: application/json{}', $output);
    }

    public function testSendResponseOutput(): void
    {
        $response = new Response();
        $response->sendResponse(
            $this->createStub(\Feast\View::class),
            $this->createStub(\Feast\Interfaces\RouterInterface::class),
            ''
        );
        $output = $this->getActualOutputForAssertion();
        $this->assertEquals('', $output);
        $this->assertEquals(200, \Feast\ResponseMock::$code);
    }

    public function testIsJson(): void
    {
        $response = new Response();
        $this->assertFalse($response->isJson());
        $response->setJson();
        $this->assertTrue($response->isJson());
    }

    public function testJsonResponse(): void
    {
        $responseItem = new \Mocks\TestJsonItem();
        $responseItem->firstName = 'FEAST';
        $response = new Response();
        $response->setJsonWithResponseObject($responseItem);
        $this->assertTrue($response->isJson());
        $response->sendResponse(
            $this->createStub(\Feast\View::class),
            $this->createStub(\Feast\Interfaces\RouterInterface::class),
            ''
        );
        $output = $this->getActualOutputForAssertion();
        $this->assertEquals('Content-type: application/json{"first_name":"FEAST","calls":null}', $output);
    }

    public function testJsonResponseReset(): void
    {
        $responseItem = new \Mocks\TestJsonItem();
        $responseItem->firstName = 'FEAST';
        $response = new Response();
        $response->setJsonWithResponseObject($responseItem);
        $this->assertTrue($response->isJson());
        $response->setJson(false);
        $this->assertFalse($response->isJson());
        $response->sendResponse(
            $this->createStub(\Feast\View::class),
            $this->createStub(\Feast\Interfaces\RouterInterface::class),
            ''
        );
        $output = $this->getActualOutputForAssertion();
        $this->assertEquals('', $output);
    }

    public function testRedirect(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $response = new Response();
        $response->redirect('/redirecting');
        $this->assertEquals('/redirecting', $response->getRedirectPath());
    }

    public function testIsValidResponseCodeTrue(): void
    {
        $this->assertTrue(ResponseCode::isValidResponseCode(200));
    }

    public function testIsValidResponseCodeFalse(): void
    {
        $this->assertFalse(ResponseCode::isValidResponseCode(2000));
    }

    public function testSetHeader(): void
    {
        $response = new Response();
        $response->setHeader('Content-type', 'application/xml');
        $response->sendResponse(
            $this->createStub(\Feast\View::class),
            $this->createStub(\Feast\Interfaces\RouterInterface::class),
            ''
        );
        $output = $this->getActualOutputForAssertion();
        $this->assertEquals('Content-type: application/xml', $output);
    }
}
