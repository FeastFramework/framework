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

namespace HttpRequest;

use Feast\Exception\InvalidArgumentException;
use Feast\Exception\ServerFailureException;
use Feast\HttpRequest\Response;
use Feast\HttpRequest\Simple;
use PHPUnit\Framework\TestCase;

class SimpleTest extends TestCase
{

    public function testMakeRequest(): void
    {
        $request = new Simple();
        $request->get('https://www.google.com/html');
        $request->makeRequest();
        $this->assertEquals(
            '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Feast Framework</title>
</head>
<body>
Test
</body>
</html>
',
            $request->getResponseAsString(),
            
        );
        $this->assertTrue($request->getResponse() instanceof Response);
    }

    public function testGetResponseAsStringEarly(): void
    {
        $request = new Simple();
        $this->assertEquals('',$request->getResponseAsString());
    }

    public function testGetResponseAsJsonEarly(): void
    {
        $request = new Simple();
        $this->assertNull($request->getResponseAsJson());
    }

    public function testGetResponseAsXmlEarly(): void
    {
        $request = new Simple();
        $this->assertNull($request->getResponseAsXml());
    }

    public function testAuthenticate(): void
    {
        $request = new Simple();
        $request->get('https://www.google.com/html');
        $this->assertNull($request->getAuthenticationString());

        $request->authenticate('feast', 'framework');
        $this->assertEquals('feast:framework', $request->getAuthenticationString());
        $request->makeRequest();
    }

    public function testAddArgument(): void
    {
        $request = new Simple();
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
        $request = new Simple();
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

    public function testAddHeader(): void
    {
        $request = new Simple();
        $request->get('https://www.google.com/html');
        $request->addHeader('Language', 'en_us');
        $this->assertEquals('en_us', $request->getHeaders()['Language']);
        $request->makeRequest();
    }

    public function testSetReferer(): void
    {
        $request = new Simple();
        $request->get('https://www.google.com/html');
        $request->setReferer('http://www.bing.com');
        $this->assertEquals('http://www.bing.com', $request->getReferer());
        $request->makeRequest();
    }

    public function testGetCookies(): void
    {
        $request = new Simple();
        $request->get('https://www.google.com/html');
        $request->addCookie('user', 'test');
        $request->addCookie('visits', '4');
        $this->assertEquals('test', $request->getCookies()['user']);
        $this->assertEquals('4', $request->getCookies()['visits']);
        $request->makeRequest();
    }

    public function testAddArgumentsPostJson(): void
    {
        $request = new Simple();
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
        $request->makeRequest();
        $this->assertEquals('POST', $request->getMethod());
    }

    public function testPut(): void
    {
        $request = new Simple();
        $request->put('https://www.google.com/html');
        $request->addArguments(
            [
                'test' => 'feast',
                'type' => ['text', 'pass']
            ]
        );

        $this->assertTrue($request->getUrl() === 'https://www.google.com');
        $this->assertEquals('PUT', $request->getMethod());
        $request->makeRequest();
    }

    public function testPatch(): void
    {
        $request = new Simple();
        $request->patch('https://www.google.com/html');
        $request->addArguments(
            [
                'test' => 'feast',
                'type' => ['text', 'pass']
            ]
        );

        $this->assertTrue($request->getUrl() === 'https://www.google.com');
        $this->assertEquals('PATCH', $request->getMethod());
        $request->makeRequest();
    }

    public function testInvalidRequest(): void
    {
        $request = new Simple();
        $this->expectException(InvalidArgumentException::class);
        $request->makeRequest();
    }
    
    public function testGetCurl(): void
    {
        $request = new Simple();
        $this->expectException(ServerFailureException::class);
        $request->getCurl();
    }
}
