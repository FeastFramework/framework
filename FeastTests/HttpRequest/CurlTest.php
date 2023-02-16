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

use Feast\Enums\RequestMethod;
use Feast\Enums\ResponseCode;
use Feast\Exception\BadRequestException;
use Feast\Exception\CurlException;
use Feast\HttpRequest\Curl;
use Feast\HttpRequest\HttpRequest;
use PHPUnit\Framework\TestCase;

class CurlTest extends TestCase
{

    public function testGet(): void
    {
        $request = new Curl();
        $request->get('http://www.google.com');
        $this->assertEquals(RequestMethod::GET, $request->getMethod());
        $this->assertTrue($request->getUrl() === 'http://www.google.com');
        /** THIS METHOD IS EXPECTED TO FAIL WITH AN EXCEPTION BECAUSE WE ARE MOCKING THE CURL ITEM WITH stdClass */
        $this->expectException(\TypeError::class);
        $curl = $request->getCurl();
    }

    public function testPatch(): void
    {
        $request = new Curl();
        $request->patch('http://www.google.com');
        $this->assertEquals(RequestMethod::PATCH, $request->getMethod());
        $this->assertTrue($request->getUrl() === 'http://www.google.com');
    }

    public function testGetUrlWithSlashes(): void
    {
        $request = new Curl();
        $request->get('http://www.google.com/test');
        $this->assertTrue($request->getUrl() === 'http://www.google.com');
    }

    public function testGetNoProtocol(): void
    {
        $request = new Curl();
        $this->expectExceptionMessage('Invalid URL passed to HttpRequest::setURL');
        $request->get('www.google.com');
    }

    public function testGetMalformedUrl(): void
    {
        $request = new Curl();
        $this->expectExceptionMessage('Error: couldn\'t reliably determine url');
        $request->get('http:///www.google.com');
    }

    public function testPost(): void
    {
        $request = new Curl();
        $request->post('http://www.google.com');
        $this->assertEquals(RequestMethod::POST, $request->getMethod());
        $this->assertTrue($request->getUrl() === 'http://www.google.com');
    }

    public function testPostJson(): void
    {
        $request = new Curl();
        $request->postJson('http://www.google.com');
        $this->assertEquals(RequestMethod::POST, $request->getMethod());
        $this->assertEquals(HttpRequest::CONTENT_TYPE_JSON, $request->getContentType());
    }

    public function testPut(): void
    {
        $request = new Curl();
        $request->put('http://www.google.com');
        $this->assertEquals(RequestMethod::PUT, $request->getMethod());
        $this->assertTrue($request->getUrl() === 'http://www.google.com');
    }

    public function testDelete(): void
    {
        $request = new Curl();
        $request->delete('http://www.google.com');
        $this->assertEquals(RequestMethod::DELETE, $request->getMethod());
        $this->assertTrue($request->getUrl() === 'http://www.google.com');
    }

    public function testSetReferer(): void
    {
        $request = new Curl();
        $request->get('https://www.google.com/html');
        $request->setReferer('http://www.bing.com');
        $this->assertEquals('http://www.bing.com', $request->getReferer());
        $request->makeRequest();
    }

    public function testAddCookie(): void
    {
        $request = new Curl();
        $request->get('http://www.google.com');
        $request->addCookie('user', 'test');
        $this->assertEquals('http://www.google.com', $request->getUrl());
    }

    public function testGetCookies(): void
    {
        $request = new Curl();
        $request->get('https://www.google.com/html');
        $request->addCookie('user', 'test');
        $request->addCookie('visits', '4');
        $this->assertEquals('test', $request->getCookies()['user']);
        $this->assertEquals('4', $request->getCookies()['visits']);
        $request->makeRequest();
    }

    public function testSetUserAgent(): void
    {
        $request = new Curl();
        $this->assertEquals(HttpRequest::DEFAULT_USER_AGENT, $request->getUserAgent());
        $request->setUserAgent('Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)');
        $this->assertEquals('Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)', $request->getUserAgent());
    }

    public function testMakeRequestCurlNotWorking(): void
    {
        $this->expectException(CurlException::class);
        $request = new Curl('https://www.google.com/not-installed');
    }

    public function testMakeRequestAndGetResponseCode(): void
    {
        $request = new Curl();
        $request->get('https://www.google.com/html');
        $request->makeRequest();
        $this->assertEquals(ResponseCode::HTTP_CODE_200, $request->getResponseCode());
        $this->assertEquals(
            str_replace("\r\n","\n",'<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Feast Framework</title>
</head>
<body>
Test
</body>
</html>
'),
            str_replace("\r\n","\n",$request->getResponseAsString())
        );
    }

    public function testGetResultAsXml(): void
    {
        $request = new Curl();
        $request->get('https://www.google.com/xml');
        $request->makeRequest();
        $this->assertEquals(ResponseCode::HTTP_CODE_200, $request->getResponseCode());
        $this->assertEquals(
            str_replace("\r\n","\n",'<?xml version="1.0"?>
<html>
<head>
    <meta charset="UTF-8"/>
    <title>Feast Framework</title>
</head>
<body>
Test
</body>
</html>
'),
            str_replace("\r\n","\n",$request->getResponseAsXml()->saveXML())
        );
    }

    public function testGetInvalidAsXml(): void
    {
        $request = new Curl();
        $request->get('https://www.google.com/json');
        $request->makeRequest();
        $this->assertEquals(ResponseCode::HTTP_CODE_200, $request->getResponseCode());
        $this->assertNull(
            $request->getResponseAsXml()
        );
    }

    public function testGetResponseCode(): void
    {
        $request = new Curl();
        $request->get('https://www.google.com/json');
        $request->makeRequest();
        $response = $request->getResponse();
        $this->assertEquals(ResponseCode::HTTP_CODE_200, $response->getResponseCode());
        $this->assertNull(
            $request->getResponseAsXml()
        );
    }

    public function testGetResultAsJson(): void
    {
        $request = new Curl();
        $request->get('https://www.google.com/json');
        $request->makeRequest();
        $this->assertEquals(ResponseCode::HTTP_CODE_200, $request->getResponseCode());
        $response = $request->getResponseAsJson();
        $this->assertEquals('feast', $response->test);
    }

    public function testGetResultAsJsonInvalid(): void
    {
        $request = new Curl();
        $request->get('https://www.google.com/html');
        $request->makeRequest();
        $this->assertEquals(ResponseCode::HTTP_CODE_200, $request->getResponseCode());
        $response = $request->getResponseAsJson();
        $this->assertNull($response);
    }

    public function testGetResultCurlFailed(): void
    {
        $request = new Curl();
        $request->get('https://www.google.com/fail');
        $request->makeRequest();
        $this->assertEquals(ResponseCode::HTTP_CODE_500, $request->getResponseCode());
        $response = $request->getResponseAsString();
        $this->assertEquals('', $response);
    }

    public function testGetResultNoUrlFailed(): void
    {
        $request = new Curl();
        $this->expectException(BadRequestException::class);
        $request->get('');
    }

    public function testAddHeader(): void
    {
        $request = new Curl();
        $request->get('https://www.google.com/html');
        $request->addHeader('Language', 'en_us');
        $this->assertEquals('en_us', $request->getHeaders()['Language']);
        $request->makeRequest();
    }

    public function testAuthenticate(): void
    {
        $request = new Curl();
        $request->get('https://www.google.com/html');
        $this->assertNull($request->getAuthenticationString());

        $request->authenticate('feast', 'framework');
        $this->assertEquals('feast:framework', $request->getAuthenticationString());
        $request->makeRequest();
    }

    public function testAddArgument(): void
    {
        $request = new Curl();
        $request->get('https://www.google.com/html');
        $request->addArgument('test', 'feast');
        $request->addArgument('type', 'text', true);
        $request->addArgument('type', 'pass', true);

        $arguments = $request->getArguments();
        $this->assertEquals('feast', $arguments['test']);
        $this->assertIsArray($arguments['type']);
        $this->assertEquals('text', $arguments['type'][0]);
        $this->assertEquals('pass', $arguments['type'][1]);
    }

    public function testAddArguments(): void
    {
        $request = new Curl();
        $request->get('https://www.google.com/html');
        $request->addArguments(
            [
                'test' => 'feast',
                'type' => ['text', 'pass']
            ]
        );

        $arguments = $request->getArguments();
        $this->assertEquals('feast', $arguments['test']);
        $this->assertIsArray($arguments['type']);
        $this->assertEquals('text', $arguments['type'][0]);
        $this->assertEquals('pass', $arguments['type'][1]);
        $request->makeRequest();
    }

    public function testSetArguments(): void
    {
        $request = new Curl();
        $request->get('https://www.google.com/html');
        $request->setArguments(
            [
                'test' => 'feast',
                'type' => ['text', 'pass']
            ]
        );

        $arguments = $request->getArguments();
        $this->assertEquals('feast', $arguments['test']);
        $this->assertIsArray($arguments['type']);
        $this->assertEquals('text', $arguments['type'][0]);
        $this->assertEquals('pass', $arguments['type'][1]);
        $request->makeRequest();
    }

    public function testAddArgumentsPut(): void
    {
        $request = new Curl();
        $request->put('https://www.google.com/html');
        $request->addArguments(
            [
                'test' => 'feast',
                'type' => ['text', 'pass']
            ]
        );

        $arguments = $request->getArguments();
        $this->assertEquals('feast', $arguments['test']);
        $this->assertIsArray($arguments['type']);
        $this->assertEquals('text', $arguments['type'][0]);
        $this->assertEquals('pass', $arguments['type'][1]);
        $request->makeRequest();
    }

    public function testAddArgumentsPatch(): void
    {
        $request = new Curl();
        $request->patch('https://www.google.com/html');
        $request->addArguments(
            [
                'test' => 'feast',
                'type' => ['text', 'pass']
            ]
        );

        $arguments = $request->getArguments();
        $this->assertEquals('feast', $arguments['test']);
        $this->assertIsArray($arguments['type']);
        $this->assertEquals('text', $arguments['type'][0]);
        $this->assertEquals('pass', $arguments['type'][1]);
        $request->makeRequest();
    }

    public function testAddArgumentsPostJson(): void
    {
        $request = new Curl();
        $request->postJson('https://www.google.com/html');
        $request->addArguments(
            [
                'test' => 'feast',
                'type' => ['text', 'pass']
            ]
        );

        $arguments = $request->getArguments();
        $this->assertEquals('feast', $arguments['test']);
        $this->assertIsArray($arguments['type']);
        $this->assertEquals('text', $arguments['type'][0]);
        $this->assertEquals('pass', $arguments['type'][1]);
        $this->assertEquals(RequestMethod::POST, $request->getMethod());
        $request->makeRequest();
    }

    public function testAddArgumentsPutJson(): void
    {
        $request = new Curl();
        $request->putJson('https://www.google.com/html');
        $request->addArguments(
            [
                'test' => 'feast',
                'type' => ['text', 'pass']
            ]
        );

        $arguments = $request->getArguments();
        $this->assertEquals('feast', $arguments['test']);
        $this->assertIsArray($arguments['type']);
        $this->assertEquals('text', $arguments['type'][0]);
        $this->assertEquals('pass', $arguments['type'][1]);
        $this->assertEquals(RequestMethod::PUT, $request->getMethod());
        $request->makeRequest();
    }

    public function testAddArgumentsPatchJson(): void
    {
        $request = new Curl();
        $request->patchJson('https://www.google.com/html');
        $request->addArguments(
            [
                'test' => 'feast',
                'type' => ['text', 'pass']
            ]
        );

        $arguments = $request->getArguments();
        $this->assertEquals('feast', $arguments['test']);
        $this->assertIsArray($arguments['type']);
        $this->assertEquals('text', $arguments['type'][0]);
        $this->assertEquals('pass', $arguments['type'][1]);
        $this->assertEquals(RequestMethod::PATCH, $request->getMethod());
        $request->makeRequest();
    }

}
