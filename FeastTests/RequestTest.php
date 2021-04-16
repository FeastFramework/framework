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

use Feast\Exception\InvalidArgumentException;
use Feast\Exception\InvalidDateException;
use Feast\Request;
use PHPUnit\Framework\TestCase;

class RequestTest extends TestCase
{

    public function setUp(): void
    {
        di(null,\Feast\Enums\ServiceContainer::CLEAR_CONTAINER);
    }

    public function testGetAllArguments(): void
    {
        $request = new Request();
        $request->setArgument('test', 'feast');
        $request->setArgument('agile', 'truth');
        $arguments = $request->getAllArguments();
        $this->assertTrue($arguments->test === 'feast');
        $this->assertTrue($arguments->agile === 'truth');
    }

    public function testGetArgumentString(): void
    {
        $request = new Request();
        $request->setArgument('test', 'feast');
        $this->assertEquals('feast', $request->getArgumentString('test'));
        $this->assertNull($request->getArgumentString('feast'));
        $this->assertEquals('truth', $request->getArgumentString('agile', 'truth'));
    }

    public function testGetArgumentInt(): void
    {
        $request = new Request();
        $request->setArgument('test', '4');
        $this->assertEquals(4, $request->getArgumentInt('test'));
        $this->assertNull($request->getArgumentInt('feast'));
        $this->assertEquals(7, $request->getArgumentInt('agile', 7));
    }

    public function testGetArgumentIntInvalid(): void
    {
        $request = new Request();
        $request->setArgument('test', 'feast');
        $this->expectException(InvalidArgumentException::class);
        $request->getArgumentInt('test');
    }

    public function testGetArgumentFloat(): void
    {
        $request = new Request();
        $request->setArgument('test', '5.4');
        $this->assertEquals(5.4, $request->getArgumentFloat('test'));
        $this->assertNull($request->getArgumentFloat('feast'));
        $this->assertEquals(7.3, $request->getArgumentFloat('agile', 7.3));
    }

    public function testGetArgumentFloatInvalid(): void
    {
        $request = new Request();
        $request->setArgument('test', 'feast');
        $this->expectException(InvalidArgumentException::class);
        $request->getArgumentFloat('test');
    }

    public function testGetArgumentBool(): void
    {
        $request = new Request();
        $request->setArgument('test', 'on');
        $this->assertTrue($request->getArgumentBool('test'));
        $this->assertNull($request->getArgumentBool('feast'));
        $this->assertFalse($request->getArgumentBool('agile', false));
    }

    public function testGetArgumentDate(): void
    {
        $request = new Request();
        $request->setArgument('test', '2009-02-14 19:34:30');
        $request->setArgument('test2', '1613325273');
        $date = $request->getArgumentDate('test');
        $this->assertEquals('20090214193430', $date->getFormattedDate('YmdHis'));
        $date = $request->getArgumentDate('test2');
        $this->assertEquals('202102', $date->getFormattedDate('Ym'));
        $this->assertNull($request->getArgumentDate('feast'));
    }

    public function testGetArgumentDateBad(): void
    {
        $request = new Request();
        $request->setArgument('test', 'feast');
        $this->assertNull($request->getArgumentDate('test'));
        $this->expectException(InvalidDateException::class);
        $this->assertNull($request->getArgumentDate('test', true));
    }

    public function testUnsetArguments(): void
    {
        $request = new Request();
        $request->setArgument('test', 'feast');
        $this->assertEquals('feast', $request->getArgumentString('test'));
        $request->setArgument('test', null);
        $this->assertNull($request->getArgumentString('feast'));
    }

    public function testIsDelete(): void
    {
        $request = new Request();
        $this->assertFalse($request->isDelete());
        $_SERVER['REQUEST_METHOD'] = 'DELETE';
        $this->assertTrue($request->isDelete());
    }

    public function testIsPut(): void
    {
        $request = new Request();
        $this->assertFalse($request->isPut());
        $_SERVER['REQUEST_METHOD'] = 'PUT';
        $this->assertTrue($request->isPut());
    }

    public function testIsPatch(): void
    {
        $request = new Request();
        $this->assertFalse($request->isPatch());
        $_SERVER['REQUEST_METHOD'] = 'PATCH';
        $this->assertTrue($request->isPatch());
    }

    public function testIsPost(): void
    {
        $request = new Request();
        $this->assertFalse($request->isPost());
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $this->assertTrue($request->isPost());
    }

    public function testIsGet(): void
    {
        $request = new Request();
        $this->assertFalse($request->isGet());
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $this->assertTrue($request->isGet());
    }

    public function testGetArgumentArrayString(): void
    {
        $request = new Request();
        $request->setArgument('test', ['feast', 'test2']);
        $this->assertEquals(['feast', 'test2'], $request->getArgumentArray('test'));
        $this->assertNull($request->getArgumentArray('feast'));
        $this->assertEquals(['7', '8'], $request->getArgumentArray('phpversions', ['7', '8']));
    }

    public function testGetArgumentArrayStringFromOnlyString(): void
    {
        $request = new Request();
        $request->setArgument('test', 'feast');
        $this->assertEquals(['feast'], $request->getArgumentArray('test'));
    }

    public function testGetArgumentArrayInt(): void
    {
        $request = new Request();
        $request->setArgument('test', ['7', '8']);
        $this->assertEquals([7, 8], $request->getArgumentArray('test', type: 'int'));
        $this->assertNull($request->getArgumentArray('feast', type: 'int'));
        $this->assertEquals([7, 8], $request->getArgumentArray('phpversions', [7, 8], 'int'));
    }

    public function testGetArgumentArrayFloat(): void
    {
        $request = new Request();
        $request->setArgument('test', ['7.4', '8.0']);
        $this->assertEquals([7.4, 8.0], $request->getArgumentArray('test', type: 'float'));
        $this->assertNull($request->getArgumentArray('feast', type: 'float'));
        $this->assertEquals([7.3, 8.1], $request->getArgumentArray('phpversions', [7.3, 8.1], 'float'));
    }

    public function testGetArgumentArrayBool(): void
    {
        $request = new Request();
        $request->setArgument('test', ['on', 'off', 'yes']);
        $this->assertEquals([true, false, true], $request->getArgumentArray('test', type: 'bool'));
        $this->assertNull($request->getArgumentArray('feast', type: 'bool'));
        $this->assertEquals([true, true], $request->getArgumentArray('agile', [true, true], 'bool'));
    }

    public function testGetArgumentArrayDate(): void
    {
        $request = new Request();
        $request->setArgument('test', ['2020-02-14 00:01:15', '1963-11-22 12:30:00']);
        $dates = $request->getArgumentArray('test', type: \Feast\Date::class);
        $this->assertEquals('20200214000115', $dates[0]->getFormattedDate('YmdHis'));
        $this->assertEquals('19631122123000', $dates[1]->getFormattedDate('YmdHis'));
        $this->assertNull($request->getArgumentArray('feast', type: 'date'));
    }

    public function testClearArguments(): void
    {
        $request = new Request();
        $request->setArgument('test', 'feast');
        $request->setArgument('agile', 'truth');
        $request->clearArguments();
        $this->assertTrue((array)$request->getAllArguments() === []);
    }
}
